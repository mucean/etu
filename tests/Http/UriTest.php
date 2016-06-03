<?php
namespace Tests\Http;

use \Etu\Http\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    protected $uri;
    public function __construct()
    {
        $url = 'http://mucean:friend@www.google.com/test/api?userId=12345#tt';
        /* @var $uri \Etu\Http\Uri */
        $this->uri = Uri::buildFromUrl($url);
    }

    public function testAllGet()
    {
        $uri = $this->uri;
        $this->assertInstanceOf('Etu\Http\Uri', $uri);
        $this->assertEquals($uri->getScheme(), 'http');
        $this->assertEquals($uri->getHost(), 'www.google.com');
        $this->assertEquals($uri->getPath(), '/test/api');
        $this->assertEquals($uri->getPort(), null);
        $this->assertEquals($uri->getQuery(), 'userId=12345');
        $this->assertEquals($uri->getFragment(), 'tt');
        $this->assertEquals($uri->getUserInfo(), 'mucean:friend');
        $this->assertEquals($uri->getAuthority(), 'mucean:friend@www.google.com');
    }

    public function testWithScheme()
    {
        $new_uri = $this->uri->withScheme('Https');
        $this->assertInstanceOf('Etu\Http\Uri', $new_uri);
        $this->assertEquals($new_uri->getScheme(), 'https');
        $this->assertEquals($new_uri->getPort(), null);
        $this->setExpectedException('InvalidArgumentException', 'scheme of Uri must be a valid value');
        $this->uri->withScheme('ftp');
    }

    public function testWithUserInfo()
    {
        $new_uri = $this->uri->withUserInfo('abc', 'def');
        $this->assertInstanceOf('Etu\Http\Uri', $new_uri);
        $this->assertEquals($new_uri->getUserInfo(), 'abc:def');
    }

    public function testWithHost()
    {
        $new_uri = $this->uri->withHost('www.google.com');
        $this->assertNotSame($new_uri, $this->uri);

        $new_uri = $this->uri->withHost('www.Baidu.com');
        $this->assertInstanceOf('Etu\Http\Uri', $new_uri);
        $this->assertEquals($new_uri->getHost(), 'www.Baidu.com');
    }

    public function testWithPort()
    {
        $new_uri = $this->uri->withPort(null);
        $this->assertNotSame($new_uri, $this->uri);

        $new_uri = $this->uri->withPort(8080);
        $this->assertInstanceOf('Etu\Http\Uri', $new_uri);
        $this->assertEquals($new_uri->getPort(), 8080);
        $this->setExpectedException('InvalidArgumentException', 'Valid Port is between 1 and 65535, 65536 given');
        $this->uri->withPort(65536);
        $this->setExpectedException('InvalidArgumentException', 'Valid Port is between 1 and 65535, -1 given');
        $this->uri->withPort(-1);
    }

    public function testWithPath()
    {
        $new_uri = $this->uri->withPath('/test/api');
        $this->assertNotSame($this->uri, $new_uri);

        $this->setExpectedException('InvalidArgumentException', 'path argument must be a string');
        $this->uri->withPath(12345);
        $this->setExpectedException('InvalidArgumentException', 'path argument must be a string');
        $this->uri->withPath($this);

        $new_uri = $this->uri->withPath('/api/aa');
        $this->assertEquals($new_uri->getPath(), '/api/aa');
        $new_uri = $this->uri->withPath('/好友/八八');
        $this->assertEquals($new_uri->getPath(), rawurlencode('/好友/八八'));
    }

    public function testWithQuery()
    {
        $new_uri = $this->uri->withQuery($this->uri->getQuery());
        $this->assertNotSame($this->uri, $new_uri);

        $this->setExpectedException('InvalidArgumentException', 'query argument must be a string');
        $this->uri->withQuery(12345);
        $this->setExpectedException('InvalidArgumentException', 'query argument must be a string');
        $this->uri->withQuery($this);

        $new_uri = $this->uri->withQuery('?test=123');
        $this->assertEquals($new_uri->getQuery(), 'test=123');
        $new_uri = $this->uri->withPath('test=哈哈');
        $this->assertEquals($new_uri->getQuery(), rawurlencode('test=哈哈'));
    }

    public function testWithFragment()
    {
        $new_uri = $this->uri->withFragment($this->uri->getFragment());
        $this->assertNotSame($this->uri, $new_uri);

        $this->setExpectedException('InvalidArgumentException', 'fragment argument must be a string');
        $this->uri->withFragment(12345);
        $this->setExpectedException('InvalidArgumentException', 'fragment argument must be a string');
        $this->uri->withFragment($this);

        $new_uri = $this->uri->withFragment('#test');
        $this->assertEquals($new_uri->getFragment(), 'test');
        $new_uri = $this->uri->withFragment('哈哈');
        $this->assertEquals($new_uri->getFragment(), rawurlencode('test=哈哈'));
    }

    public function testToString()
    {
        $this->assertEquals((string) $this->uri, 'http://mucean:friend@www.google.com/test/api?userId=12345#tt');
        $new_uri = $this->uri->withFragment('哈哈');
        $this->assertEquals(
            (string) $new_uri,
            'http://mucean:friend@www.google.com/test/api?userId=12345#' . rawurlencode('哈哈')
        );
    }
}
