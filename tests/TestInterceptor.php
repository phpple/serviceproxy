<?php
/**
 *
 * @author: ronnie
 * @since: 2019/4/1 9:53 PM
 * @copyright: 2019@100tal.com
 * @filesource: TestInterceptor.php
 */

namespace phpple\apiproxy\tests;


use phpple\apiproxy\IInterceptor;
use phpple\apiproxy\proxies\IProxy;

class TestInterceptor implements IInterceptor
{

    /**
     * Before method called
     * @param IProxy $proxy
     * @param $name
     * @param $args
     * @return mixed
     */
    public function before(IProxy $proxy, $name, $args)
    {
        var_dump(__METHOD__.':'.$name. var_export($args, true));
    }

    /**
     * After method called
     * @param IProxy $proxy
     * @param $name
     * @param $args
     * @param $ret
     * @return mixed
     */
    public function after(IProxy $proxy, $name, $args, $ret)
    {
        var_dump(__METHOD__.':'.$name.';'.var_export($ret, true));
    }

    /**
     * Exception found
     * @param IProxy $proxy
     * @param $name
     * @param $args
     * @return mixed
     */
    public function exception(IProxy $proxy, $name, $args)
    {
        var_dump(__METHOD__);
    }
}