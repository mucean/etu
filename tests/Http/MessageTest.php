<?php

namespace Tests\Http;

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
    }
}
