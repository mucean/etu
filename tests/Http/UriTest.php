<?php

namespace Tests\Http;

use \Etu\Http\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $url = 'http://mucean:friend@www.google.com/test/api?userId=12345#tt';
        /* @var $uri \Etu\Http\Uri */
        $uri = Uri::buildFromUrl($url);
        $this->assertInstanceOf('Etu\Http\Uri', $uri);
        $this->assertEquals($uri->getScheme(), 'http');
        $this->assertEquals($uri->getHost(), 'www.google.com');
        $this->assertEquals($uri->getPath(), '/test/api');
        $this->assertEquals($uri->getPort(), null);
        $this->assertEquals($uri->getQuery(), 'userId=12345');
        $this->assertEquals($uri->getFragment(), 'tt');
        $this->assertEquals($uri->getUserInfo(), 'mucean:friend');
        $this->assertEquals($uri->getAuthority(), 'mucean:friend@www.google.com');

        $new_uri = $uri->withScheme('https');
        $this->assertInstanceOf('Etu\Http\Uri', $new_uri);
        $this->assertEquals($new_uri->getScheme(), 'https');
        $this->assertEquals($new_uri->getPort(), null);

        $new_uri = $uri->withUserInfo('abc', 'def');
        $this->assertInstanceOf('Etu\Http\Uri', $new_uri);
        $this->assertEquals($new_uri->getUserInfo(), 'abc:def');
    }
}
