<?php
/**
 *
 * @author: ronnie
 * @since: 2019/4/1 9:18 PM
 * @copyright: 2019@100tal.com
 * @filesource: BaseProxy.php
 */

namespace phpple\apiproxy\proxies;


class BaseProxy implements IProxy
{
    private $mod = null;
    // 协议名称，用来做不同的来源的proxy的界定
    private $protocol = '';

    /**
     * BaseProxy constructor.
     * @param $mod
     * @param string $protocol
     */
    public function __construct($mod, $protocol = '')
    {
        $this->mod = $mod;
        $this->protocol = $protocol;
    }

    /**
     * @see IProxy::init
     */
    public function init(array $conf, array $params = [])
    {
        // TODO
    }

    /**
     * @see IProxy::call
     */
    public function call(string $name, array $args)
    {
        return null;
    }


    /**
     * @see IProxy::getMod
     */
    public function getMod()
    {
        return $this->mod;
    }

    /**
     * Protocol
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }


    /**
     * @see IProxy::cacheable
     */
    public function cacheable()
    {
        return true;
    }
}