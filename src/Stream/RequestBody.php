<?php

namespace Etu\Stream;

use Etu\Stream;

class RequestBody extends Stream
{
    public function __construct()
    {
        $stream = fopen('php://temp', 'w+');
        parent::__construct($stream);
    }
}
