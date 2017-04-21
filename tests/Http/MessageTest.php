<?php
namespace Tests\Http;

use Etu\Http\Message;
use Etu\Stream;

class MessageTest extends \PHPUnit\Framework\TestCase
{
    protected $context;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @before
     */
    public function buildMessage()
    {
        $this->context = BuildContext::getContext();
        $this->message = new MessageMock($this->context);
    }

    public function testGetProtocolVersion()
    {
        $message = $this->message;
        $this->assertEquals($message->getProtocolVersion(), '1.1');
    }

    public function testWithProtocolVersion()
    {
        $message = $this->message;
        $protocol = '2.0';
        $newMessage = $message->withProtocolVersion($protocol);
        $this->assertEquals($newMessage->getProtocolVersion(), $protocol);
        $wrongProtocol = '5.5';
        $this->expectExceptionMessage('protocol must be one of them, 1.1, 1.0, 2.0');
        $message->withProtocolVersion($wrongProtocol);
    }

    public function testGetHeaders()
    {
        $message = $this->message;
        $data = [];
        foreach (BuildContext::$context as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $data[$name] = [$value];
            }
        }

        $this->assertEquals($data, $message->getHeaders());
    }

    public function testHasHeader()
    {
        $message = $this->message;
        $this->assertTrue($message->hasHeader('host'));
    }

    public function testGetHeader()
    {
        $message = $this->message;
        $this->assertEquals($message->getHeader('host')[0], $this->context->get('HTTP_HOST'));
    }

    public function testGetHeaderLine()
    {
        $message = $this->message;
        $this->assertEquals($message->getHeaderLine('host'), $this->context->get('HTTP_HOST'));
    }

    public function testWithHeader()
    {
        $message = $this->message;
        $this->assertEquals($message->getHeaderLine('hi'), '');
        $newMessage = $message->withHeader('hi', 'hello, world!');
        $this->assertEquals($newMessage->getHeaderLine('hi'), 'hello, world!');
        $newMessage = $message->withHeader('hi', ['hello', 'world']);
        $this->assertEquals($newMessage->getHeaderLine('hi'), 'hello,world');
    }

    public function testWithHeaderValueException()
    {
        $message = $this->message;
        $this->expectExceptionMessage(
            'header value must be an type can be convert to string or an array contains string value'
        );
        $message->withHeader('hi', $this);
    }

    public function testWithHeaderOtherValueException()
    {
        $message = $this->message;
        $this->expectExceptionMessage('header array value must only contains an type can be convert to string');
        $message->withHeader('hi', ['hello', $this]);
    }

    public function testWithAddedHeader()
    {
        $message = $this->message;
        $this->assertEquals($message->getHeaderLine('host'), $this->context->get('HTTP_HOST'));
        // with same host
        $newMessage = $message->withAddedHeader('host', $this->context->get('HTTP_HOST'));
        $this->assertEquals($newMessage->getHeaderLine('host'), $this->context->get('HTTP_HOST'));
        // with same host
        $newMessage = $message->withAddedHeader('host', [$this->context->get('HTTP_HOST')]);
        $this->assertEquals($newMessage->getHeaderLine('host'), $this->context->get('HTTP_HOST'));
        // with other host
        $newMessage = $message->withAddedHeader('host', 'www.mucean.com');
        $this->assertEquals(
            $newMessage->getHeaderLine('host'),
            $this->context->get('HTTP_HOST') . ',' . 'www.mucean.com'
        );
    }

    public function testWithoutHeader()
    {
        $message = $this->message;
        $newMessage = $message->withHeader('hi', 'hello, world!');
        $this->assertEquals($newMessage->getHeaderLine('hi'), 'hello, world!');
        $newMessage = $newMessage->withoutHeader('hi');
        $this->assertEquals($newMessage->getHeaderLine('hi'), '');
    }

    public function testGetBody()
    {
        $message = $this->message;
        $this->assertInstanceOf('Etu\Stream', $message->getBody());
    }

    public function testWithBody()
    {
        $message = $this->message;
        $stream = new Stream(fopen('php://input', 'r'));
        $newMessage = $message->withBody($stream);
        $this->assertInstanceOf('Tests\Http\MessageMock', $newMessage);
        $this->assertInstanceOf('Etu\Stream', $newMessage->getBody());
    }
}
