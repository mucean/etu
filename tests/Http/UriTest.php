<?php

namespace Tests\Http;

use \Etu\Http\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    protected $urls = [
        'http://www.google.com/test/api?userId=12345#tt'
    ];

    public function testAll()
    {
        foreach ($this->urls as $url) {
            $uri = Uri::buildFromUrl($url);
            $this->assertInstanceOf('Etu\Http\Uri', $uri);
        }
    }
}
