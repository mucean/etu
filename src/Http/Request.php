<?php

namespace Etu\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Etu\Http\Message;
use Etu\Stream;
use InvalidArgumentException;
use Closure;
use Etu\Http\Uri;
use Etu\Http\Context;

class Request extends Message implements ServerRequestInterface
{
    protected $servers;
    protected $cookies;
    protected $uploadedFiles;
    protected $parsedBody = false;
    protected $queryParams;
    protected $attributes = [];
    protected $requestTarget;
    protected $originalMethod;
    protected $method;

    protected $mediaType = [];

    protected $validMethod = ['GET', 'POST', 'PUT', 'DELETE', 'CONNECT', 'HEAD', 'OPTIONS', 'PATCH', 'TRACE'];

    protected $uri;

    public static function buildFromContext(Context $context)
    {
        $bodyStream = fopen('php://input', 'r');
        $bodyStream = new Stream($bodyStream);
        $uri = Uri::buildFromContext($context);
        $uploadedFiles = UploadedFile::buildFromContext();
        return new static($context->all(), $_COOKIE, $bodyStream, $uri, $uploadedFiles);
    }

    public function __construct(
        array $servers,
        array $cookies,
        StreamInterface $body,
        UriInterface $uri,
        array $uploadedFiles = []
    ) {
        $this->servers = $servers;
        $this->cookies = $cookies;
        $this->body = $body;
        $this->uri = $uri;
        $this->uploadedFiles = $uploadedFiles;
        $this->originalMethod = $this->servers['REQUEST_METHOD'];
        $this->setHeaders(getallheaders($this->servers));
        if (!$this->hasHeader('host') && isset($_SERVER['SERVER_NAME'])) {
            $this->withHeader('Host', $_SERVER['SERVER_NAME']);
        }

        if (isset($this->servers['SERVER_PROTOCOL'])) {
            $this->protocol = substr($this->servers['SERVER_PROTOCOL'], 5);
        }

        $this->addMediaTypeParser('multipart/form-data', function ($body) {
            parse_str($body, $data);
            return $data;
        });

        $this->addMediaTypeParser('application/x-www-form-urlencoded', function ($body) {
            parse_str($body, $data);
            return $data;
        });

        $this->addMediaTypeParser('application/json', function ($body) {
            return json_decode($body, true);
        });

        $this->addMediaTypeParser('application/xml', function ($body) {
            $backup = libxml_disable_entity_loader(true);
            $result = simplexml_load_string($body);
            libxml_disable_entity_loader($backup);
            return $result;
        });

        $this->addMediaTypeParser('text/xml', function ($body) {
            $backup = libxml_disable_entity_loader(true);
            $result = simplexml_load_string($body);
            libxml_disable_entity_loader($backup);
            return $result;
        });
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
                $this->uri = $this->getUri();
            }
            $query = $this->uri->getQuery();
        }

        parse_str($query, $this->queryParams);

        return $this->queryParams;
    }

    public function withQueryParams(array $query)
    {
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

        $this->parsedBody = null;
        $mediaType = $this->getMediaType();
        if (isset($this->mediaType[$mediaType])) {
            $this->parsedBody = $this->mediaType[$mediaType]($this->getBody());
        }

        return $this->parsedBody;
    }

    public function withParsedBody($data)
    {
        if (!is_array($data) && !is_null($data) && !is_object($data)) {
            throw new \InvalidArgumentException(
                'Parsed body must be an array type, an object type or null'
            );
        }

        $new = clone $this;

        $new->parsedBody = $data;

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
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function withoutAttribute($name)
    {
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
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = $uri;

        $host = $uri->getHost();
        if (!$preserveHost && $host) {
            if ($port = $uri->getPort()) {
                $host .= ':' . $port;
            }
            $this->withHeader('Host', $host);
        }
    }

    public function getContentType()
    {
        $contentType = $this->getHeader('content_type');
        return $contentType ? $contentType[0] : null;
    }

    public function getMediaType()
    {
        $contentType = $this->getContentType();

        $mediaType = null;
        if ($contentType) {
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $mediaType = $contentTypeParts[0];
        }

        return $mediaType;
    }

    public function addMediaTypeParser($type, callable $parser)
    {
        if ($parser instanceof Closure) {
            $parser = $parser->bindTo($this, $this);
        }
        $this->mediaType[(string) $type] = $parser;
    }
}
