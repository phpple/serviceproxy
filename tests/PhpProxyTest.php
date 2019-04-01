<?php
/**
 *
 * @author: ronnie
 * @since: 2019/4/1 9:38 PM
 * @copyright: 2019@100tal.com
 * @filesource: PhpProxyTest.php
 */

namespace phpple\apiproxy\tests;

use phpple\apiproxy\ServiceProxy;
use phpple\apiproxy\tests\service\FooService;
use PHPUnit\Framework\TestCase;

class PhpProxyTest extends TestCase
{
    /**
     * @var FooService
     */
    private $fooService;

    public function testHello()
    {
        ServiceProxy::init([
            'mod' => [],
            'servers' => [],
        ], [
            'ns_root' => 'phpple\\apiproxy\\tests\\service'
        ]);
        ServiceProxy::setGlobalInterceptors([new TestInterceptor()]);
        $this->fooService = ServiceProxy::getProxy('FooService');
        $result = $this->fooService->foo('world');
        $this->assertEquals('Hello, world', $result);
    }
}
