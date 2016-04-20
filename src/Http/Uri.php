<?php

namespace Etu\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected $scheme;
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    protected $path;
    protected $query;
    protected $fragment;

    protected static $standardPort = [
        'http' => 80,
        'https' => 443
    ];

    public function __construct($url)
    {
        $urlComponent = parse_url($url);
        if ($urlComponent === false) {
            throw new \Exception('Class Uri construct with a valid url');
        }
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getAuthority()
    {
        if (empty($this->host)) {
            return '';
        }

        $authority = $this->user;
        if (!empty($this->pass)) {
            $authority .= ':' . $this->pass;
        }

        $authority .= '@' . $this->host;

        if ($this->port !== null && $this->validatePort($this->scheme, $this->host, $this->port)) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo()
    {
        if (empty($this->user)) {
            return '';
        }

        $userInfo = $this->user;

        if (!empty($this->pass)) {
            $userInfo .= ':' . $this->pass;
        }

        return $userInfo;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    protected function applyComponent(array $urlComponent)
    {
        $this->scheme = isset($urlComponent['scheme'])
            ? $urlComponent['scheme']
            : '';
        $this->host = isset($urlComponent['host'])
            ? strtolower($urlComponent['host'])
            : '';
        $this->port = isset($urlComponent['port'])
            ? $this->normalizePort($this->scheme, $this->host, $urlComponent['port'])
            : null;
        $this->user = isset($urlComponent['user'])
            ? $urlComponent['user']
            : '';
        $this->pass = isset($urlComponent['pass'])
            ? $urlComponent['pass']
            : '';
        $this->path = isset($urlComponent['path'])
            ? $urlComponent['path']
            : '';
        $this->query = isset($urlComponent['query'])
            ? $urlComponent['query']
            : '';
        $this->fragment = isset($urlComponent['fragment'])
            ? $urlComponent['fragment']
            : '';
    }

    protected function normalizePort($scheme, $host, $port)
    {
        if ($port === null) {
            return null;
        }

        $port = (int) $port;

        if (1 > $port || $port > 65535) {
            throw new \InvalidArgumentException(
                sprintf('Valid Port is between 1 and 65535, %d given', $port)
            );
        }

        return $this->validatePort($scheme, $host, $port) ? $port : null;
    }

    protected function validatePort($scheme, $host, $port)
    {
        if (!$scheme && $port) {
            return true;
        }

        if (!$host || !$port) {
            return false;
        }

        return !isset(static::$standardPort[$scheme]) || $port !== static::$standardPort[$scheme];
    }
}
