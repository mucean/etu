<?php
namespace Etu\Stream;

use Etu\Stream;

class RequestBody extends Stream
{
    public function __construct()
    {
        $stream = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $stream);
        parent::__construct($stream);
    }
}
