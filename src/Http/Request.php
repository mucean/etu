<?php

namespace Etu\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Etu\Http\Uri;

class Request extends ServerRequestInterface
{
    use MessageTrait;

    protected $servers = [];
    protected $cookies = [];

    protected $uri = null;

    public function __construct()
    {
        $this->servers = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->setHeaders(getallheaders());
        if (!$this->hasHeader('host') && isset($_SERVER['SERVER_NAME'])) {
            $this->withHeader('Host', $_SERVER['SERVER_NAME']);
        }
    }

    public function getServerParams()
    {
        return $this->servers;
    }

    public function getCookieParams()
    {
        return $this->cookies;
    }

    public function withCookieParams(array $cookies)
    {
        if ($this->cookies === $cookies) {
            return $this;
        }

        $new = clone $this;
        $new->cookies = $cookies;
        return $new;
    }

    public function getQueryParams()
    {
        if (isset($this->servers['QUERY_STRING'])) {
            return $this->servers['QUERY_STRING'];
        }
        if ($this->uri === null) {
            $this->uri = $this->getRequestUri();
        }

        return $this->uri->getQuery();
    }

    public function getRequestUri()
    {
        $uri = new Uri;
        $servers = &$this->servers;
        $uri->withScheme(!empty($servers['HTTPS'] && $servers['HTTPS'] == 'on' ? 'https' : 'http'));
        $uri->withHost(!empty($servers['SERVER_NAME'])
            ? $servers['SERVER_NAME']
            : $this->hasHeader('host') ? $this->getHeader('host') : '');
        $uri->withPort(!empty($servers['SERVER_PORT']) ? $servers['SERVER_PORT'] : null);

        $path = $query = '';
        if (!empty($servers['REQUEST_URI'])) {
            $requestUri = $servers['REQUEST_URI'];
            if (($pos = strpos($requestUri, '?')) !== false) {
                $path = substr($requestUri, 0, $pos);
                $query = substr($requestUri, $pos + 1);
            } else {
                $path = $requestUri;
                $query = '';
            }
        } else {
            $path = $servers['PHP_SELF'];
            if (isset($servers['argv'])) {
                $query = $servers['argv'][0];
            } elseif (isset($servers['QUERY_STRING'])) {
                $query = $servers['QUERY_STRING'];
            }
        }
        $uri->withPath($path);
        $uri->withQuery($query);
        return $uri;
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
                $host .= ':' . $port;
            }
            $this->withHeader('Host', $host);
        }
    }
}
