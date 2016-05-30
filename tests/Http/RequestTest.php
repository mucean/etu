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

    /**
     * @depends testBuildFromContext
     */
    public function testWithCookieParams(Request $request)
    {
        $newCookie = ['hi' => 'hello, world!'];
        $newRequest = $request->withCookieParams($newCookie);
        $this->assertNotSame($newRequest, $request);
        $this->assertEquals($newCookie, $newRequest->getCookieParams());
    }

    /**
     * @depends testBuildFromContext
     */
    public function testGetQueryParams(Request $request)
    {
        $_GET = ['aa' => 'bb', 'cc' => ['dd', 'ee']];
        $this->assertEquals($_GET, $request->getQueryParams());
    }

    /**
     * @depends testBuildFromContext
     */
    public function testWithQueryParams(Request $request)
    {
        $testQueryParams = ['hi' => 'Hello, world!'];
        $newRequest = $request->withQueryParams($testQueryParams);
        $this->assertNotSame($request, $newRequest);
        $this->assertEquals($newRequest->getQueryParams(), $testQueryParams);
    }

    /**
     * @depends testBuildFromContext
     */
    public function testGetUploadedFiles(Request $request)
    {
        $this->assertEquals([], $request->getUploadedFiles());
    }
}
