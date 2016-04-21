<?php

namespace Etu\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected $scheme = false;
    protected $host = false;
    protected $port = false;
    protected $user = false;
    protected $pass = false;
    protected $path = false;
    protected $query = false;
    protected $fragment = false;
    protected $component = false;

    protected static $standardPort = [
        'http' => 80,
        'https' => 443
    ];

    public function __construct($url)
    {
        $this->component = parse_url($url);
        if ($this->component === false) {
            throw new \Exception('Class Uri construct with a valid url');
        }
    }

    public function getScheme()
    {
        if ($this->scheme === false) {
            $this->scheme = isset($this->component['scheme'])
                ? $this->component['scheme']
                : '';
        }
        return $this->scheme;
    }

    public function getAuthority()
    {
        if (empty($this->getHost())) {
            return '';
        }

        $authority = $this->host;
        if ($userInfo = $this->getUserInfo()) {
            $authority = $userInfo . '@' . $this->host;
        }

        if ($this->getPort() !== null && $this->validatePort($this->scheme, $this->host, $this->port)) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo()
    {
        if ($this->user === false) {
            $this->user = isset($this->component['user'])
                ? $this->component['user']
                : '';
            $this->pass = isset($this->component['pass'])
                ? $this->component['pass']
                : '';
        }

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
        if ($this->host === false) {
            $this->host = isset($this->component['host'])
                ? strtolower($this->component['host'])
                : '';
        }
        return $this->host;
    }

    public function getPort()
    {
        if ($this->port === false) {
            $this->port = isset($this->component['port'])
                ? $this->normalizePort($this->scheme, $this->host, $this->component['port'])
                : null;
        }
        return $this->port;
    }

    public function getPath()
    {
        if ($this->path === false) {
            $this->path = isset($this->component['path'])
                ? $this->component['path']
                : '';
        }
    }

    protected function applyComponent()
    {
        $this->query = isset($this->component['query'])
            ? $this->component['query']
            : '';
        $this->fragment = isset($this->component['fragment'])
            ? $this->component['fragment']
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
