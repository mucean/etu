<?php

namespace Tests\Http;

use Etu\Stream;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $message = new MessageMock(BuildContext::getContext());
        $this->assertInstanceOf('Tests\Http\MessageMock', $message);
        return $message;
    }

    /**
     * @depends testConstruct
     */
    public function testGetProtocolVersion(MessageMock $message)
    {
        $this->assertEquals($message->getProtocolVersion(), '1.1');
    }

    /**
     * @depends testConstruct
     */
    public function testWithProtocolVersion(MessageMock $message)
    {
        $protocol = '2.0';
        $newMessage = $message->withProtocolVersion($protocol);
        $this->assertEquals($newMessage->getProtocolVersion(), $protocol);
        $wrongProtocol = '5.5';
        $this->setExpectedException(
            'InvalidArgumentException',
            'protocol must be one of them, 1.1, 1.0, 2.0'
        );
        $message->withProtocolVersion($wrongProtocol);
    }

    /**
     * @depends testConstruct
     */
    public function testGetHeaders(MessageMock $message)
    {
        $this->assertEquals(array_keys($message->getHeaders()), array_keys(getallheaders(BuildContext::$context)));
    }

    /**
     * @depends testConstruct
     */
    public function testHasHeader(MessageMock $message)
    {
        $this->assertTrue($message->hasHeader('host'));
    }

    /**
     * @depends testConstruct
     */
    public function testGetHeader(MessageMock $message)
    {
        $this->assertEquals($message->getHeader('host')[0], getallheaders(BuildContext::$context)['Host']);
    }

    /**
     * @depends testConstruct
     */
    public function testGetHeaderLine(MessageMock $message)
    {
        $this->assertEquals($message->getHeaderLine('host'), getallheaders(BuildContext::$context)['Host']);
    }

    /**
     * @depends testConstruct
     */
    public function testWithHeader(MessageMock $message)
    {
        $this->assertEquals($message->getHeaderLine('hi'), '');
        $newMessage = $message->withHeader('hi', 'hello, world!');
        $this->assertEquals($newMessage->getHeaderLine('hi'), 'hello, world!');
        $newMessage = $message->withHeader('hi', ['hello', 'world']);
        $this->assertEquals($newMessage->getHeaderLine('hi'), 'hello,world');
        $this->setExpectedException(
            'InvalidArgumentException',
            'header name must be a string or has __toString function when use withHeader function set a header'
        );
        $message->withHeader([], 'error');
    }

    /**
     * @depends testConstruct
     */
    public function testWithHeaderNameException(MessageMock $message)
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'header name must be a string or has __toString function when use withHeader function set a header'
        );
        $message->withHeader($this, 'error');
    }

    /**
     * @depends testConstruct
     */
    public function testWithHeaderValueException(MessageMock $message)
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'header value must be an type can be convert to string or an array contains string value'
        );
        $message->withHeader('hi', $this);
    }

    /**
     * @depends testConstruct
     */
    public function testWithHeaderOtherValueException(MessageMock $message)
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'header array value must only contains an type can be convert to string'
        );
        $message->withHeader('hi', ['hello', $this]);
    }

    /**
     * @depends testConstruct
     */
    public function testWithAddedHeader(MessageMock $message)
    {
        $this->assertEquals($message->getHeaderLine('host'), getallheaders(BuildContext::$context)['Host']);
        // with same host
        $newMessage = $message->withAddedHeader('host', getallheaders(BuildContext::$context)['Host']);
        $this->assertEquals($newMessage->getHeaderLine('host'), getallheaders(BuildContext::$context)['Host']);
        // with same host
        $newMessage = $message->withAddedHeader('host', [getallheaders(BuildContext::$context)['Host']]);
        $this->assertEquals($newMessage->getHeaderLine('host'), getallheaders(BuildContext::$context)['Host']);
        // with other host
        $newMessage = $message->withAddedHeader('host', 'www.mucean.com');
        $this->assertEquals(
            $newMessage->getHeaderLine('host'),
            getallheaders(BuildContext::$context)['Host'] . ',' . 'www.mucean.com'
        );
    }

    /**
     * @depends testConstruct
     */
    public function testWithoutHeader(MessageMock $message)
    {
        $this->assertEquals($message->getHeaderLine('hi'), '');
        $newMessage = $message->withHeader('hi', 'hello, world!');
        $this->assertEquals($newMessage->getHeaderLine('hi'), 'hello, world!');
        $newMessage = $newMessage->withoutHeader('hi');
        $this->assertEquals($newMessage->getHeaderLine('hi'), '');
    }

    /**
     * @depends testConstruct
     */
    public function testGetBody(MessageMock $message)
    {
        $this->assertInstanceOf('Etu\Stream', $message->getBody());
    }

    /**
     * @depends testConstruct
     */
    public function testWithBody(MessageMock $message)
    {
        $stream = new Stream(fopen('php://input', 'r'));
        $newMessage = $message->withBody($stream);
        $this->assertInstanceOf('Tests\Http\MessageMock', $newMessage);
        $this->assertInstanceOf('Etu\Stream', $newMessage->getBody());
    }
}
