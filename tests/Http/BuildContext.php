<?php

namespace Tests\Http;

use Etu\Http\Context;

class BuildContext
{
    public static $context = [
        'USER' => 'www',
        'HOME' => '/home/www',
        'HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.8',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
        'HTTP_DNT' => '1',
        'HTTP_ACCEPT' => '*/*',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.97 Safari/537.36',
        'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_CONTENT_LENGTH' => '0',
        'HTTP_CONNECTION' => 'keep-alive',
        'HTTP_HOST' => 'etu.mucean.com',
        'SCRIPT_FILENAME' => '/data/work/my/etu-test/index.php',
        'REDIRECT_STATUS' => '200',
        'SERVER_NAME' => 'etu.mucean.com',
        'SERVER_PORT' => '80',
        'SERVER_ADDR' => '127.0.0.1',
        'REMOTE_PORT' => '38722',
        'REMOTE_ADDR' => '127.0.0.1',
        'SERVER_SOFTWARE' => 'nginx/1.8.1',
        'GATEWAY_INTERFACE' => 'CGI/1.1',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'DOCUMENT_ROOT' => '/data/work/my/etu-test/public',
        'DOCUMENT_URI' => '/app/test',
        'REQUEST_URI' => '/app/test?aa=bb&cc[]=dd&cc[]=ee',
        'SCRIPT_NAME' => '/app/test',
        'CONTENT_LENGTH' => '0',
        'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
        'REQUEST_METHOD' => 'PUT',
        'QUERY_STRING' => 'aa=bb&cc[]=dd&cc[]=ee',
        'FCGI_ROLE' => 'RESPONDER',
        'PHP_SELF' => '/app/test',
        'REQUEST_TIME_FLOAT' => 1464250809.863632,
        'REQUEST_TIME' => 1464250809,
        'test' => ['hi' => 'hello']
    ];

    public static function getContext()
    {
        return new Context(static::$context);
    }
}
