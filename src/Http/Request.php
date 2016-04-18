<?php

namespace Etu\Http;

use Psr\Http\Message\ServerRequestInterface;

class Request extends ServerRequestInterface
{
    use MessageTrait;

    protected $servers = [];

    public function __construct()
    {
        $this->servers = $_SERVER;
        foreach ($this->servers as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_name = str_replace(
                    ' ',
                    '-',
                    ucwords(strtolower(str_replace(
                        '_',
                        ' ',
                        substr($key, 5)
                    )))
                );
                $this->headers[$header_name] = $value;
                $this->header_lines[strtolower($header_name)] = $value;
            }
        }
    }
}
