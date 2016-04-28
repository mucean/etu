<?php

namespace Etu\Http;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected static $charsUnreserved = 'a-zA-Z0-9\-\._~';
    protected static $charsSubDelim = '!\$&\'\(\)\*\+,;=';
    protected static $standardPort = [
        'http' => 80,
        'https' => 443
    ];

    protected $scheme = false;
    protected $host = false;
    protected $port = false;
    protected $user = false;
    protected $pass = false;
    protected $path = false;
    protected $query = false;
    protected $fragment = false;

    public function __construct($url = null)
    {
        if ($url === null) {
            return $this;
        }
        $component = parse_url($url);
        if ($component === false) {
            throw new \Exception('Class Uri construct with a valid url');
        }
        $this->applyComponent($component);
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

        $authority = $this->host;
        if ($userInfo = $this->getUserInfo()) {
            $authority = $userInfo . '@' . $this->host;
        }

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

    public function getPath()
    {
        return $this->path;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function withScheme($scheme)
    {
        $scheme = trim(strtolower($scheme));
        if ($scheme === $this->scheme) {
            return $this;
        }
        $this->scheme = $scheme;
        $this->port = $this->normalizePort($scheme, $this->host, $this->port);
        return $this;
    }

    public function withUserInfo($user, $password = null)
    {
        $this->user = $user;
        if ($password !== null) {
            $this->pass = $password;
        }

        return $this;
    }

    public function withHost($host)
    {
        $this->host = strtolower($host);
    }

    public function withPort($port)
    {
        $this->port = $this->normalizePort($this->scheme, $this->host, $port);
        return $this;
    }

    public function withPath($path)
    {
        if (DEBUG) {
            if (!is_string($path) && !method_exists($path, '__toString')) {
                throw new \InvalidArgumentException(
                    'path argument must be a string'
                );
            }
        }
        $this->path = $this->normalizePath($path);
        return $this;
    }

    public function withQuery($query)
    {
        if (DEBUG) {
            if (!is_string($query) && !method_exists($query, '__toString')) {
                throw new \InvalidArgumentException(
                    'query argument must be a string'
                );
            }
        }
        if (strpos($query, '?') === 0) {
            $query = substr($query, 1);
        }
        $this->query = $this->normalizeQueryAndFragment($query);
        return $this;
    }

    public function withFragment($fragment)
    {
        if (DEBUG) {
            if (!is_string($fragment) && !method_exists($fragment, '__toString')) {
                throw new \InvalidArgumentException(
                    'fragment argument must be a string'
                );
            }
        }
        if (strpos($fragment, '#') === 0) {
            $fragment = substr($fragment, 1);
        }

        $this->fragment = $this->normalizeQueryAndFragment($fragment);
        return $this;
    }

    public function __toString()
    {
        return $this->createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->path,
            $this->query,
            $this->fragment
        );
    }

    /**
     * Create a URI string from its various parts
     *
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     * @return string
     */
    private function createUriString($scheme, $authority, $path, $query, $fragment)
    {
        $uri = '';

        if (!empty($scheme)) {
            $uri .= $scheme . '://';
        }

        if (!empty($authority)) {
            $uri .= $authority;
        }

        if ($path != null) {
            if ($uri && substr($path, 0, 1) !== '/') {
                $uri .= '/';
            }
            $uri .= $path;
        }

        if ($query != null) {
            $uri .= '?' . $query;
        }

        if ($fragment != null) {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    protected function applyComponent(&$component)
    {
        $this->scheme = isset($component['scheme'])
            ? $component['scheme']
            : '';
        $this->user = isset($component['user'])
            ? $component['user']
            : '';
        $this->pass = isset($component['pass'])
            ? $component['pass']
            : '';
        $this->host = isset($component['host'])
            ? strtolower($component['host'])
            : '';
        $this->port = isset($component['port'])
            ? $this->normalizePort($this->scheme, $this->host, $component['port'])
            : null;
        $this->path = isset($component['path'])
            ? $this->normalizePath($component['path'])
            : '';
        $this->query = isset($component['query'])
            ? $this->normalizeQueryAndFragment($component['query'])
            : '';
        $this->fragment = isset($component['fragment'])
            ? $this->normalizeQueryAndFragment($component['fragment'])
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

    protected function normalizePath($path)
    {
        $preg = '/(?:[^' . self::$charsUnreserved . self::$charsSubDelim . ':@\/%]+|%(?![a-zA-Z0-9]{2}))/';
        return preg_replace_callback($preg, function ($matches) {
            return rawurlencode($matches[0]);
        }, $path);
    }

    protected function normalizeQueryAndFragment($str)
    {
        $preg = '/(?:[^' . self::$charsUnreserved . self::$charsSubDelim . ':@\/%\?]+|%(?![A-Fa-f0-9]{2}))/';
        return preg_replace_callback($preg, function ($matches) {
            return rawurlencode($matches[0]);
        }, $str);
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
