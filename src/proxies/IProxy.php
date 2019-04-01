<?php
/**
 *
 * @author: ronnie
 * @since: 2019/4/1 9:19 PM
 * @copyright: 2019@100tal.com
 * @filesource: IProxy.php
 */

namespace phpple\apiproxy\proxies;


interface IProxy
{
    /**
     * Get the mode name
     * @return string
     */
    public function getMod();

    /**
     * Initialize
     * @param $conf
     * @param $params
     * @return mixed
     */
    public function init(array $conf, array $params = []);

    /**
     * Call method
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function call(string $name, array $args);

    /**
     * Cacheable
     * @return mixed
     */
    public function cacheable();
}