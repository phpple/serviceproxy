<?php
/**
 *
 * @author: ronnie
 * @since: 2019/4/1 9:10 PM
 * @copyright: 2019@100tal.com
 * @filesource: ServiceProxy.php.php
 */

namespace phpple\apiproxy;

use phpple\apiproxy\proxies\IProxy;

class ServiceProxy
{
    private $caller = null;
    private static $proxies = [];
    private static $configure = [];
    private static $nsroot = '';
    private $intercepters = [];
    private static $globalInterceptors = [];
    private static $encoding = 'UTF-8';
    const DEFAULT_PROXY = 'PhpServiceProxy';

    private function __construct(IProxy $proxy)
    {
        $this->caller = $proxy;
    }

    /**
     * 初始化
     * @param array $conf
     * <code>array(
     *    'mod'=>array(
     *        'class'      => 'RpcProxy|HttpRpcProxy|HttpJsonProxy',
     *        'options'    => []
     *    ),
     *  'server' => array(
     *
     *  ),
     * )</code>
     * @param array $pathroot
     * <code>array(
     *    'ns_root' =>  , // app模块的根目录
     * )</code>
     */
    public static function init(array $conf, array $pathroot)
    {
        $mods = $conf['mod'];
        $servers = $conf['servers'];
        foreach ($mods as $mod => $cfg) {
            if (!empty($cfg['server'])) {
                $cfg['server'] = $servers[$cfg['server']];
            }
            self::$configure[$mod] = $cfg;
        }
        if (!empty($conf['encoding'])) {
            $encoding = strtoupper($conf['encoding']);
            self::$encoding = str_replace('-', '', $encoding);
        }

        self::$nsroot = $pathroot['ns_root'];
    }

    /**
     * 设置全局的拦截器，针对每个模块有效
     *
     * @param mixed $intercepters
     * @static
     * @access public
     * @return void
     */
    public static function setGlobalInterceptors(array $intercepters)
    {
        self::$globalInterceptors = $intercepters;
    }

    /**
     * 获取模块
     * @param string $mod 模块名
     * @param array $param 初始化参数
     * @return ServiceProxy
     * @throws Exception
     */
    public static function getProxy(string $mod, array $param = [])
    {
        if (isset(self::$proxies[$mod])) {
            //支持注册一个Proxy来接口的伪实现
            //自动化测试的时候可以用到
            $proxy = self::$proxies[$mod];
        } else {
            if (!($proxy = self::getProxyFromConf($mod, $param))) {
                //默认按照php来实现
                $clsName = __NAMESPACE__.'\\proxies\\'.self::DEFAULT_PROXY;
                $proxy = new $clsName($mod);
                $proxy->init(array(
                    'ns_root' => self::$nsroot
                ), $param);
            }
            if ($proxy->cacheable()) {
                self::registerProxy($proxy);
            }
        }
        $api = new ServiceProxy($proxy);
        foreach (self::$globalInterceptors as $intercepter) {
            $api->addInterceptor($intercepter);
        }
        return $api;
    }

    /**
     * 通过配置获取代理
     * @param string $mod
     * @param array $param
     * @return mixed
     * @throws Exception
     */
    private static function getProxyFromConf(string $mod, array $param)
    {
        $conf = false;
        $protocol = '';
        if (!isset(self::$configure[$mod])) {
            if (($pos = strpos($mod, '://')) !== false) {
                $protocol = substr($mod, 0, $pos);
                $key = $protocol . '://';
                if (isset(self::$configure[$key])) {
                    $conf = self::$configure[$key];
                    $mod = substr($mod, $pos + 3);
                }
            }
        } else {
            $conf = self::$configure[$mod];
        }
        if (!$conf) {
            if (isset(self::$configure['*'])) {
                $conf = self::$configure['*'];
            }
        }
        if (!$conf) {
            return false;
        }

        if (empty($conf['class'])) {
            throw new Exception('apiproxy.errconf mod=' . $mod);
        }
        $internalmod = $mod;
        if (!empty($conf['mod'])) {
            //模块重命名
            $internalmod = $conf['mod'];
        }
        $class = __NAMESPACE__ . "\\" . $conf['class'];
        if (!empty($conf['server'])) {
            $options = $conf['server'];
        } else {
            $options = $conf;
        }
        unset($conf['class'], $conf['server'], $conf['mod']);
        $options = array_merge($options, $conf);
        //为了支持多级模块，/转换成:
        $internalmod = str_replace('/', ':', $internalmod);
        $options['encoding'] = self::$encoding;
        $object = new $class($internalmod, $protocol);
        $object->init($options, $param);
        return $object;
    }

    /**
     * 动态调用方法
     * @param $name
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        try {
            $this->callInterceptor('before', $name, $args);

            $ret = $this->caller->call($name, $args);
            $this->callInterceptor('after', $name, $args, $ret);
            return $ret;
        } catch (\Exception $ex) {
            $this->callInterceptor('exception', $name, $args);
            throw $ex;
        }
    }

    /**
     * 调用中断器
     * @param $method
     * @param $callName
     * @param $args
     * @param null $ret
     */
    private function callInterceptor($method, $callName, $args, $ret = null)
    {
        $intercepters = $this->intercepters;
        foreach ($intercepters as $intercepter) {
            if ($method == 'after') {
                $intercepter->$method($this->caller, $callName, $args, $ret);
            } else {
                $intercepter->$method($this->caller, $callName, $args);
            }
        }
    }

    /**
     * 添加一个拦截器。类型名称相同的会被覆盖。
     * @param IInterceptor $intercepter
     * @return void
     */
    public function addInterceptor(IInterceptor $intercepter)
    {
        $classname = get_class($intercepter);
        $this->intercepters[$classname] = $intercepter;
    }

    /**
     * 删除一个拦截器。删除时会根据传入对象的类型名称来判断。
     * 如果类型相同的拦截器会被删除。
     * @param IInterceptor $intercepter
     * @return void
     */
    public function removeInterceptor(IInterceptor $intercepter)
    {
        $classname = get_class($intercepter);
        if (isset($this->intercepters[$classname])) {
            if ($intercepter == $this->intercepters[$classname]) {
                unset($this->intercepters[$classname]);
            }
        }
    }

    /**
     * 获取所有拦截器
     * @return array
     */
    public function getInterceptors()
    {
        return $this->intercepters;
    }

    /**
     * 注册一个代理
     * @param IProxy $proxy
     */
    public static function registerProxy(IProxy $proxy)
    {
        $mod = $proxy->getMod();
        $protocol = $proxy->getProtocol();
        if ($protocol) {
            $mod = $protocol . '//' . $mod;
        }
        self::$proxies[$mod] = $proxy;
    }
}
