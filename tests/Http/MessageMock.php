<?php
namespace Tests\Http;

use Etu\Http\Context;
use Etu\Http\Message;
use Etu\Stream;

class MessageMock extends Message
{
    public function __construct(Context $context)
    {
        if ($context->has('SERVER_PROTOCOL')) {
            $this->protocol = substr($context['SERVER_PROTOCOL'], 5);
        }

        $bodyStream = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $bodyStream);
        $this->body = new Stream($bodyStream);

        $headers = $context->all();
        $this->setHeaders(getallheaders($headers));
    }
}
