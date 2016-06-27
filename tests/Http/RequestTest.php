<?php
namespace Tests\Http;

use Etu\Http\Request;
use Etu\Http\Uri;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $request;

    protected $context;

    /**
     * @before
     */
    public function buildRequest()
    {
        $this->context = BuildContext::getContext();
        $this->request = Request::buildFromContext($this->context);
    }

    public function testBuildFromContext()
    {
        $this->assertInstanceOf('Etu\Http\Request', $this->request);
    }

    public function testGetServerParams()
    {
        $originalServer = BuildContext::$context;
        $this->assertEquals($originalServer, $this->request->getServerParams());
    }

    public function testGetCookieParams()
    {
        $request = $this->request;
        $this->assertEquals($_COOKIE, $request->getCookieParams());
        $this->assertEquals([], $request->getCookieParams());
    }

    public function testWithCookieParams()
    {
        $request = $this->request;
        $newCookie = ['hi' => 'hello, world!'];
        $newRequest = $request->withCookieParams($newCookie);
        $this->assertNotSame($newRequest, $request);
        $this->assertEquals($newCookie, $newRequest->getCookieParams());
    }

    public function testGetQueryParams()
    {
        $request = $this->request;
        $_GET = ['aa' => 'bb', 'cc' => ['dd', 'ee']];
        $this->assertEquals($_GET, $request->getQueryParams());
    }

    public function testWithQueryParams()
    {
        $request = $this->request;
        $testQueryParams = ['hi' => 'Hello, world!'];
        $newRequest = $request->withQueryParams($testQueryParams);
        $this->assertNotSame($request, $newRequest);
        $this->assertEquals($newRequest->getQueryParams(), $testQueryParams);
    }

    public function testGetUploadedFiles()
    {
        $uploadedFileTest = new UploadedFileTest();
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

    public function testWithParsedBody()
    {
        $request = $this->request;
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

    public function testGetAttributes()
    {
        $this->assertEquals($this->request->getAttributes(), []);
    }

    public function testGetAttribute()
    {
        $this->assertEquals($this->request->getAttribute('hi', 'Hello, world!'), 'Hello, world!');
    }

    public function testWithAttribute()
    {
        $request = $this->request;
        $value = 'Hello, world!';
        $this->assertEquals($request->getAttribute('hi', $value), $value);
        $newRequest = $request->withAttribute('hi', $value);
        $this->assertEquals($newRequest->getAttribute('hi'), $value);
    }

    public function testWithoutAttribute()
    {
        $request = $this->request;
        $value = 'Hello, world!';
        $newRequest = $request->withAttribute('hi', $value);
        $this->assertEquals($newRequest->getAttribute('hi'), $value);
        $otherNewRequest = $newRequest->withoutAttribute('hi');
        $this->assertEquals($otherNewRequest->getAttribute('hi'), null);
    }

    public function testGetRequestTarget()
    {
        $request = $this->request;
        $this->assertEquals(
            $request->get('servers', ['REQUEST_URI']),
            rawurldecode($request->getRequestTarget())
        );
    }

    public function testWithRequestTarget()
    {
        $newRequest = $this->request->withRequestTarget('/hello/world');
        $this->assertEquals(
            rawurldecode($newRequest->getRequestTarget()),
            '/hello/world'
        );
    }

    public function testGetMethod()
    {
        $this->assertEquals('PUT', $this->request->getMethod());
        $otherRequest = Request::buildFromContext(BuildContext::getContext([
            'REQUEST_METHOD' => 'GET',
            'HTTP_X_Http_Method_Override' => 'POST',
        ]));
        $this->assertEquals('POST', $otherRequest->getMethod());
    }

    public function testWithMethod()
    {
        $newRequest = $this->request->withMethod('DELETE');
        $this->assertEquals($newRequest->getMethod(), 'DELETE');
        $this->setExpectedException('InvalidArgumentException', 'request method must be a string');
        $this->request->withMethod([]);
    }

    public function testWithMethodException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Request method must be a valid method');
        $this->request->withMethod('hello');
    }

    public function testGetUri()
    {
        $this->assertInstanceOf('Etu\Http\Uri', $this->request->getUri());
    }

    public function testWithUri()
    {
        $request = $this->request;
        $newRequest = $request->withUri(Uri::buildFromUrl('http://www.mucean.com:90/abc/def?aa=bb#heihei'));
        $this->assertEquals($newRequest->getHeaderLine('host'), 'www.mucean.com:90');
        $newRequest = $request->withUri(Uri::buildFromUrl('http://www.mucean.com:90/abc/def?aa=bb#heihei'), true);
        $this->assertEquals($newRequest->getHeaderLine('host'), $request->getHeaderLine('host'));
    }

    public function requestProvider()
    {
        return [
            [
                Request::buildFromContext(BuildContext::getContext(['REQUEST_METHOD' => 'GET'])),
                [],
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'GET',
                    'HTTP_CONTENT_TYPE' => 'abcdefg',
                ])),
                null,
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'GET',
                    'HTTP_CONTENT_TYPE' => 'multipart/form-data',
                ])),
                ['aa' => 'bb', 'cc' => ['dd', 'ee']],
                'aa=bb&cc[]=dd&&cc[]=ee',
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'PUT',
                    'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                ])),
                ['aa' => 'bb', 'cc' => ['dd', 'ee']],
                'aa=bb&cc[]=dd&&cc[]=ee',
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                ])),
                ['aa' => 'bb'],
                '',
                ['aa' => 'bb'],
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'POST',
                    'HTTP_CONTENT_TYPE' => 'multipart/form-data',
                ])),
                ['aa' => 'bb'],
                '',
                ['aa' => 'bb'],
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'DELETE',
                    'HTTP_CONTENT_TYPE' => 'application/json',
                ])),
                ['aa' => 'bb'],
                '{"aa":"bb"}',
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'DELETE',
                    'HTTP_CONTENT_TYPE' => 'application/xml',
                ])),
                simplexml_load_string('<language><best>php</best></language>'),
                '<language><best>php</best></language>',
            ],
            [
                Request::buildFromContext(BuildContext::getContext([
                    'REQUEST_METHOD' => 'DELETE',
                    'HTTP_CONTENT_TYPE' => 'text/xml',
                ])),
                simplexml_load_string('<language><best>php</best></language>'),
                '<language><best>php</best></language>',
            ],
        ];
    }
}
