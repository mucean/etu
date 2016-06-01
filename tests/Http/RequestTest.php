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

    public function testGetUploadedFiles()
    {
        $uploadedFileTest = new UploadedFileTest;
        $testFile = $uploadedFileTest->contextProvider()[0];
        $_FILES = $testFile[0];
        $this->uploadedFiles = $testFile[1];
        $request = Request::buildFromContext(BuildContext::getContext());
        $this->assertEquals($this->uploadedFiles, $request->getUploadedFiles());
    }

    /**
     * @dataProvider requestProvider
     */
    public function testGetParsedBody(Request $request, $expect, $stream = '', $post = [])
    {
        $_POST = $post;
        if ($stream) {
            $body = $request->getBody();
            $body->write($stream);
        }
        $this->assertEquals($request->getParsedBody(), $expect);
    }

    /**
     * @depends testBuildFromContext
     */
    public function testWithParsedBody(Request $request)
    {
        $newRequest = $request->withParsedBody(null);
        $this->assertEquals($newRequest->getParsedBody(), null);
        $newRequest = $request->withParsedBody(['hi' => 'hello, world!']);
        $this->assertEquals($newRequest->getParsedBody(), ['hi' => 'hello, world!']);
        $newRequest = $request->withParsedBody($this);
        $this->assertEquals($newRequest->getParsedBody(), $this);
        $this->setExpectedException(
            'InvalidArgumentException',
            'Parsed body must be an array type, an object type or null'
        );
        $request->withParsedBody('error');
    }

    /**
     * @depends testBuildFromContext
     */
    public function testGetAttributes(Request $request)
    {
        $this->assertEquals($request->getAttributes(), []);
    }

    /**
     * @depends testBuildFromContext
     */
    public function testGetAttribute(Request $request)
    {
        $this->assertEquals($request->getAttribute('hi', 'Hello, world!'), 'Hello, world!');
    }

    /**
     * @depends testBuildFromContext
     */
    public function testWithAttribute(Request $request)
    {
        $value = 'Hello, world!';
        $this->assertEquals($request->getAttribute('hi', $value), $value);
        $newRequest = $request->withAttribute('hi', $value);
        $this->assertEquals($newRequest->getAttribute('hi'), $value);
    }

    /**
     * @depends testBuildFromContext
     */
    public function testWithoutAttribute(Request $request)
    {
        $value = 'Hello, world!';
        $newRequest = $request->withAttribute('hi', $value);
        $this->assertEquals($newRequest->getAttribute('hi'), $value);
        $otherNewRequest = $newRequest->withoutAttribute('hi');
        $this->assertEquals($otherNewRequest->getAttribute('hi'), null);
    }

    /**
     * @depends testBuildFromContext
     */
    public function testGetRequestTarget(Request $request)
    {
        $this->assertEquals(
            $request->get('servers', ['REQUEST_URI']),
            rawurldecode($request->getRequestTarget())
        );
    }

    /**
     * @depends testBuildFromContext
     */
    public function testWithRequestTarget(Request $request)
    {
        $newRequest = $request->withRequestTarget('/hello/world');
        $this->assertEquals(
            rawurldecode($newRequest->getRequestTarget()),
            '/hello/world'
        );
    }

    public function requestProvider()
    {
        return [
            [
                Request::buildFromContext(BuildContext::getContext(['REQUEST_METHOD' => 'GET'])),
                []
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'GET',
                    'HTTP_CONTENT_TYPE' => 'multipart/form-data'
                ])),
                ['aa' => 'bb', 'cc' => ['dd', 'ee']],
                'aa=bb&cc[]=dd&&cc[]=ee'
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'PUT',
                    'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded'
                ])),
                ['aa' => 'bb', 'cc' => ['dd', 'ee']],
                'aa=bb&cc[]=dd&&cc[]=ee'
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded'
                ])),
                ['aa' => 'bb'],
                '',
                ['aa' => 'bb']
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_CONTENT_TYPE' => 'multipart/form-data'
                ])),
                ['aa' => 'bb'],
                '',
                ['aa' => 'bb']
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'DELETE',
                    'HTTP_CONTENT_TYPE' => 'application/json'
                ])),
                ['aa' => 'bb'],
                '{"aa":"bb"}',
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'DELETE',
                    'HTTP_CONTENT_TYPE' => 'application/xml'
                ])),
                simplexml_load_string('<language><best>php</best></language>'),
                '<language><best>php</best></language>',
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'DELETE',
                    'HTTP_CONTENT_TYPE' => 'text/xml'
                ])),
                simplexml_load_string('<language><best>php</best></language>'),
                '<language><best>php</best></language>',
            ]
        ];
    }
}
