<?php

namespace Etu\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Etu\Http\Uri;
use Etu\Http\UploadedFile;
use Etu\Stream;

class Request implements ServerRequestInterface
{
    use MessageTrait;

    protected $servers = [];
    protected $cookies = [];
    protected $get = [];
    protected $post = [];
    protected $files = [];
    protected $uploadedFiles;
    protected $parsedBody = false;
    protected $attributes = [];
    protected $requestTarget;

    protected $uri = null;

    public function __construct()
    {
        $this->servers = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
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
        return $this->get;
        /* if (isset($this->servers['QUERY_STRING'])) {
            $query = $this->servers['QUERY_STRING'];
        } else {
            if ($this->uri === null) {
                $this->uri = $this->getRequestUri();
            }
            $query = $this->uri->getQuery();
        }

        if ($query === '') {
            return [];
        }
        parse_str($query, $res);
        return $res; */
    }

    public function withQueryParams(array $query)
    {
        if ($this->get === $query) {
            return $this;
        }

        $new = clone $this;
        $new->get = $query;
        return $new;
    }

    public function getUploadedFiles()
    {
        if ($this->uploadedFiles !== null) {
            return $this->uploadedFiles;
        }
        $files = [];
        foreach ($this->files as $file) {
            $files[] = new UploadedFile($file);
        }
        $this->uploadedFiles = $files;
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {
        if ($this->uploadedFiles === $uploadedFiles) {
            return $this;
        }

        foreach ($uploadedFiles as $file) {
            if (!$file instanceof UploadedFile) {
                throw new \InvalidArgumentException(
                    '$uploadedFiles must be an array tree of UploadedFileInterface instances'
                );
            }
        }
        $new = clone $this;
        $this->uploadedFiles = $uploadedFiles;
        $new_files = [];
        foreach ($uploadedFiles as $file) {
            $new_files[] = $file->file;
        }
        $new->files = $new_files;
        return $new;
    }

    public function getParsedBody()
    {
        if (!$this->parsedBody !== false) {
            return $this->parsedBody;
        }

        if ($this->getMethod() === 'post') {
            $contentType = strtolower($this->getHeaderLine('content_type'));
            if (strpos($contentType, 'application/x-www-form-urlencoded') !== false ||
                strpos($contentType, 'multipart/form-data') !== false) {
                return $this->parsedBody = $this->post;
            }
        }

        $body = (string) $this->getBody();

        if ($body === '') {
            return $this->parsedBody = null;
        }

        $parseBody = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $parseBody;
        }

        return null;
    }

    public function withParsedBody($data)
    {
        if ($this->getParsedBody() === $data) {
            return $this;
        }

        if (!is_array($data) || !is_null($data)) {
            throw new \InvalidArgumentException(
                'Argument $data must be a array or null parsed from getParsedBody method'
            );
        }

        $new = clone $this;

        if (is_array($data)) {
            $contentType = $this->getHeaderLine('content_type');
            if ($contentType === 'application/x-www-form-urlencoded' || $contentType === 'multipart/form-data') {
                return $new->post = $data;
            }
            $body = json_encode($data, JSON_UNESCAPED_UNICODE);
            $new = $new->withBody($body);
        } else {
            $new = $new->withBody(new Stream());
        }

        return $new;
    }

    public function getAttributes()
    {
        $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name]) ?
            $this->attributes[$name] :
            $default;
    }

    public function withAttribute($name, $value)
    {
        if ($this->getAttribute($name) === $value) {
            return $this;
        }

        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute($name)
    {
        if (!isset($this->attributes[$name])) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }

    public function getRequestTarget()
    {
        if ($this->uri === null) {
            return '/';
        }

        $target = $this->uri->getPath();
        if ($query = $this->uri->getQuery()) {
            $target = $target . '?' . $query;
        }

        return $this->requestTarget = $target;
    }

    public function withRequestTarget($requestTarget)
    {
        if ($this->requestTarget === $requestTarget) {
            return $this;
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    public function getRequestUri()
    {
        if ($this->uri !== null) {
            return $this->uri;
        }
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
        return $this->uri = $uri;
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
