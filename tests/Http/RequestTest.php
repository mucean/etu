<?php

namespace Tests\Http;

use Etu\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildFromContext()
    {
        $context = BuildContext::getContext();
        $request = Request::buildFromContext($context);
        $this->assertInstanceOf('Etu\Http\Request', $request);
        return $request;
    }

    /**
     * @depends testBuildFromContext
     */
    public function testGetServerParams(Request $request)
    {
        $originalServer = BuildContext::$context;
        $this->assertEquals($originalServer, $request->getServerParams());
    }

    /**
     * @depends testBuildFromContext
     */
    public function testGetCookieParams(Request $request)
    {
        $this->assertEquals($_COOKIE, $request->getCookieParams());
        $this->assertEquals([], $request->getCookieParams());
    }
}
