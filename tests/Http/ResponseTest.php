<?php
namespace Tests\Http;

use Etu\Http\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $response = new Response();
        $this->assertInstanceOf('Etu\Http\Response', $response);

        return $response;
    }

    /**
     * @depends testConstruct
     */
    public function testGetStatusCode(Response $response)
    {
        $this->assertEquals($response->getStatusCode(), 200);
    }

    /**
     * @depends testConstruct
     */
    public function getReasonPhrase(Response $response)
    {
        $this->assertEquals($response->getReasonPhrase(), 'OK');
    }

    /**
     * @depends testConstruct
     */
    public function testWithStatus(Response $response)
    {
        $newResponse = $response->withStatus(404, 'hello, world!');
        $this->assertEquals($newResponse->getStatusCode(), 404);
        $this->assertEquals($newResponse->getReasonPhrase(), 'hello, world!');
        $newResponse = $response->withStatus(404);
        $this->assertEquals($newResponse->getReasonPhrase(), 'Not Found');
        $newResponse = $response->withStatus(555);
        $this->assertEquals($newResponse->getReasonPhrase(), '');
        $this->setExpectedException('InvalidArgumentException', 'Invalid status code');
        $response->withStatus(900);
    }
}
