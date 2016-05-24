<?php

namespace Etu\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Etu\Http\Uri;
use Etu\Http\UploadedFile;
use Etu\Stream;
use InvalidArgumentException;

class Request implements ServerRequestInterface
{
    use MessageTrait;

    protected $servers;
    protected $cookies;
    protected $uploadedFiles;
    protected $parsedBody = false;
    protected $queryParams;
    protected $attributes = [];
    protected $requestTarget;
    protected $originalMethod;
    protected $method;

    protected $validMethod = ['GET', 'POST', 'PUT', 'DELETE', 'CONNECT', 'HEAD', 'OPTIONS', 'PATCH', 'TRACE'];

    protected $uri = null;

    public function __construct(
        array $servers,
        array $cookies,
        UriInterface $uri,
        array $uploadedFiles = []
    ) {
        $this->servers = $servers;
        $this->cookies = $cookies;
        $this->uri = $uri;
        $this->get = $_GET;
        $this->uploadedFiles = $uploadedFiles;
        $this->originalMethod = $this->servers['REQUEST_METHOD'];
        $this->setHeaders(getallheaders($this->servers));
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
        if ($this->queryParams !== null) {
            return $this->queryParams;
        }

        if (isset($this->servers['QUERY_STRING'])) {
            $query = $this->servers['QUERY_STRING'];
        } else {
            if ($this->uri === null) {
                $this->uri = $this->getRequestUri();
            }
            $query = $this->uri->getQuery();
        }

        parse_str($query, $this->queryParams);

        return $this->queryParams;
    }

    public function withQueryParams(array $query)
    {
        if ($this->queryParams === $query) {
            return $this;
        }

        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    public function getUploadedFiles()
    {
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
        return $new;
    }

    public function getParsedBody()
    {
        if (!$this->parsedBody !== false) {
            return $this->parsedBody;
        }

        if ($this->originalMethod === 'post') {
            $contentType = strtolower($this->getHeaderLine('content_type'));
            if (strpos($contentType, 'application/x-www-form-urlencoded') !== false ||
                strpos($contentType, 'multipart/form-data') !== false) {
                return $this->parsedBody = $_POST;
            }
        }

        // TODO need different function to unserize the request body

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
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

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

    public function getMethod()
    {
        if ($this->method !== null) {
            return $this->method;
        }

        $this->method = $this->originalMethod;
        $overMethod = $this->getHeaderLine('X-Http-Method-Override');

        if ($overMethod) {
            $this->method = $this->filterMethod($overMethod);
        }

        return $this->method;
    }

    public function withMethod($method)
    {
        if ($this->method === $method) {
            return $this;
        }

        $method = $this->filterMethod($method);

        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    protected function filterMethod($method)
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException('request method must be a string');
        }

        $method = strtoupper($method);

        if (in_array($method, $this->validMethod)) {
            return $method;
        }

        throw new InvalidArgumentException('Request method must be a valid method');
    }

    public function getUri()
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
