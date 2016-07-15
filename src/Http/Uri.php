<?php
namespace Etu\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected static $charsUnreserved = 'a-zA-Z0-9\-\._~';
    protected static $charsSubDelim = '!\$&\'\(\)\*\+,;=';
    protected static $standardPort = [
        'http' => 80,
        'https' => 443,
    ];

    protected $scheme = false;
    protected $host = false;
    protected $port = false;
    protected $user = false;
    protected $pass = false;
    protected $path = false;
    protected $query = false;
    protected $fragment = false;

    /**
     * @var string $url
     * @return self
     */
    public static function buildFromUrl($url = null)
    {
        if (null === $url) {
            return null;
        }

        if (!is_string($url)) {
            throw new InvalidArgumentException(
                sprintf('build a uri instance need a url of string type, %s given', gettype($url))
            );
        }

        $scheme = $host = $path = $query = $fragment = $user = $pass = '';
        $port = null;
        $component = parse_url($url);

        if (false === $component) {
            throw new \Exception('Class Uri construct with a valid url');
        }

        extract($component);

        return new static($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }

    public static function buildFromContext(Context $context)
    {
        $scheme = 'http';

        if ($context->has('HTTPS') && $context->get('HTTPS') !== 'off') {
            $scheme = 'https';
        }

        $host = '';

        if ($context->has('HTTP_HOST')) {
            $host = $context->get('HTTP_HOST');
        } elseif ($context->has('SERVER_NAME')) {
            $host = $context->get('SERVER_NAME');
        }

        $port = $context->get('SERVER_PORT');

        $pos = strpos($host, ':');

        if (false !== $pos) {
            $port = (int) substr($host, $pos + 1);
            $host = substr($host, 0, $pos);
        }

        $requestUri = $context->get('REQUEST_URI');

        if ($requestUri) {
            $pos = strpos($requestUri, '?');

            if (false !== $pos) {
                $path = substr($requestUri, 0, $pos);
            } else {
                $path = $requestUri;
            }
        } else {
            $path = $context->get('SCRIPT_NAME', '');
        }

        $query = $context->get('QUERY_STRING', '');
        $fragment = '';

        $user = $context->get('PHP_AUTH_USER', '');
        $pass = $context->get('PHP_AUTH_PW', '');

        return new static($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }

    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '',
        $query = '',
        $fragment = '',
        $user = '',
        $pass = ''
    ) {
        $this->scheme = empty($scheme) ? '' : $this->normalizeScheme($scheme);
        $this->host = $host;
        $this->port = $this->normalizePort($scheme, $host, $port);
        $this->path = empty($path) ? '' : $this->normalizePath($path);
        $this->query = empty($query) ? '' : $this->normalizeQueryAndFragment($query);
        $this->fragment = empty($fragment) ? '' : $this->normalizeQueryAndFragment($fragment);
        $this->user = $user;
        $this->pass = $pass;
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

        if (null !== $this->port && $this->validatePort($this->scheme, $this->host, $this->port)) {
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

        $new = clone $this;
        $new->scheme = empty($scheme) ? '' : $new->normalizeScheme($scheme);
        $new->port = null;

        return $new;
    }

    public function withUserInfo($user, $password = null)
    {
        $new = clone $this;
        $new->user = $user;

        if (null !== $password) {
            $new->pass = $password;
        }

        return $new;
    }

    public function withHost($host)
    {
        $new = clone $this;
        $new->host = $host;

        return $new;
    }

    public function withPort($port)
    {
        $new = clone $this;
        $new->port = $new->normalizePort($new->scheme, $new->host, $port);

        return $new;
    }

    public function withPath($path)
    {
        if (!is_string($path) && !method_exists($path, '__toString')) {
            throw new InvalidArgumentException(
                'path argument must be a string'
            );
        }

        $new = clone $this;
        $new->path = $new->normalizePath($path);

        return $new;
    }

    public function withQuery($query)
    {
        if (!is_string($query) && !method_exists($query, '__toString')) {
            throw new InvalidArgumentException(
                'query argument must be a string'
            );
        }

        if (strpos($query, '?') === 0) {
            $query = substr($query, 1);
        }

        $new = clone $this;
        $new->query = $new->normalizeQueryAndFragment($query);

        return $new;
    }

    public function withFragment($fragment)
    {
        if (!is_string($fragment) && !method_exists($fragment, '__toString')) {
            throw new InvalidArgumentException(
                'fragment argument must be a string'
            );
        }

        if (strpos($fragment, '#') === 0) {
            $fragment = substr($fragment, 1);
        }

        $new = clone $this;
        $new->fragment = $new->normalizeQueryAndFragment($fragment);

        return $new;
    }

    public function __toString()
    {
        return $this->createUriString();
    }

    /**
     * Create a URI string from its various parts
     *
     * @return string
     */
    private function createUriString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->path;
        $query = $this->query;
        $fragment = $this->fragment;
        $uri = '';

        if (!empty($scheme)) {
            $uri .= $scheme . '://';
        }

        if (!empty($authority)) {
            $uri .= $authority;
        }

        if (null != $path) {
            if ($uri && substr($path, 0, 1) !== '/') {
                $uri .= '/';
            }

            $uri .= $path;
        }

        if (null != $query) {
            $uri .= '?' . $query;
        }

        if (null != $fragment) {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    protected function normalizeScheme($scheme)
    {
        if (!is_string($scheme) || method_exists($scheme, '__toString')) {
            throw new InvalidArgumentException('scheme of Uri must be a string');
        }

        $scheme = strtolower(str_replace('://', '', $scheme));

        if (!$this->validateScheme($scheme)) {
            throw new InvalidArgumentException('scheme of Uri must be a valid value');
        }

        return $scheme;
    }

    protected function normalizePort($scheme, $host, $port)
    {
        if (null === $port) {
            return null;
        }

        $port = (int) $port;

        if (1 > $port || $port > 65535) {
            throw new InvalidArgumentException(
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

    protected function validateScheme($scheme)
    {
        if ('' === $scheme) {
            return true;
        }

        $validScheme = array_keys(static::$standardPort);

        if (in_array($scheme, $validScheme)) {
            return true;
        }

        return false;
    }

    protected function validatePort($scheme, $host, $port)
    {
        if (!$scheme && $port) {
            return true;
        }

        if (!$host || !$port) {
            return false;
        }

        return !isset(static::$standardPort[$scheme]) || static::$standardPort[$scheme] !== $port;
    }
}
