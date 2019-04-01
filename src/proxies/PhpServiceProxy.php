<?php
/**
 *
 * @author: ronnie
 * @since: 2019/4/1 9:30 PM
 * @copyright: 2019@100tal.com
 * @filesource: PhpServiceProxy.phpoxy.php
 */

namespace phpple\apiproxy\proxies;


class PhpServiceProxy extends BaseProxy
{
    private $srcCaller = null;

    /**
     * @see IProxy::init
     */
    public function init(array $conf, array $params = [])
    {
        $nsroot = $conf['ns_root'];

        $mod = $this->getMod();
        //支持多级的子模块
        $modseg = explode('/', $mod);
        //类名以每一级单词大写开始
        $class = rtrim($nsroot, "\\") . "\\" . implode("\\", $modseg);

        $this->srcCaller = new $class($params);
    }

    /**
     * @see IProxy::call
     */
    public function call(string $name, array $args)
    {
        $ret = call_user_func_array(array($this->srcCaller, $name), $args);
        return $ret;
    }
}