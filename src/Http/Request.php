<?php

namespace Etu\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends ServerRequestInterface
{
    use MessageTrait;

    protected $servers = [];

    protected $uri = null;

    public function __construct()
    {
        $this->servers = $_SERVER;
        $this->setHeaders(getallheaders());
        if (!$this->hasHeader('host') && isset($_SERVER['SERVER_NAME'])) {
            $this->withHeader('Host', $_SERVER['SERVER_NAME']);
        }
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($this->uri === $uri) {
            return $this;
        }

        $this->uri = $uri;

        $host = $uri->getHost();
        if (!$preserveHost && $host) {
            if ($port = $uri->getPort()) {
                $host = $host . $port;
            }
            $this->withHeader('Host', $host);
        }
    }
}
