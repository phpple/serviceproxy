<?php
/**
 *
 * @author: ronnie
 * @since: 2019/4/1 9:40 PM
 * @copyright: 2019@100tal.com
 * @filesource: FooService.php
 */

namespace phpple\apiproxy\tests\service;


class FooService
{
    /**
     * return hello,$name
     * @param $name
     * @return string
     */
    public function foo($name)
    {
        return 'Hello, '.$name;
    }
}