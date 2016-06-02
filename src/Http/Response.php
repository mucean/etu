<?php

namespace Etu\Response;

use Psr\Http\Message\ResponseInterface;
use InvalidArgumentException;

class Response extends Message implements ResponseInterface
{
    protected $statusCode = 200;

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
