<?php
/**
 * 中断器
 * @author: ronnie
 * @since: 2019/4/1 9:32 PM
 * @copyright: 2019@100tal.com
 * @filesource: IInterceptor.php
 */

namespace phpple\apiproxy;


use phpple\apiproxy\proxies\IProxy;

interface IInterceptor
{
    /**
     * Before method called
     * @param IProxy $proxy
     * @param $name
     * @param $args
     * @return mixed
     */
    public function before(IProxy $proxy, $name, $args);

    /**
     * After method called
     * @param IProxy $proxy
     * @param $name
     * @param $args
     * @param $ret
     * @return mixed
     */
    public function after(IProxy $proxy, $name, $args, $ret);

    /**
     * Exception found
     * @param IProxy $proxy
     * @param $name
     * @param $args
     * @return mixed
     */
    public function exception(IProxy $proxy, $name, $args);
}