<?php

namespace Etu\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\MessageInterface;

abstract class Message implements MessageInterface
{
    protected $protocol = '1.1';

    protected $headers = [];

    protected $body;

    protected $headerLines = [];

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $this->validateProtocol($version);
        $new->protocol = $version;
        return $new;
    }

    public function getHeaders()
    {
        return $this->headerLines;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function getHeader($name)
    {
        return $this->hasHeader($name) ?
            $this->headers[strtolower($name)] :
            [];
    }

    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader($name, $value)
    {
        if (!is_string($name) || method_exists($name, '__toString')) {
            throw new InvalidArgumentException(
                'header name must be a string or has __toString function when use withHeader function set a header'
            );
        }

        $name = trim((string) $name);

        $new = clone $this;

        $new->headers[strtolower($name)] = $new->normalizeHeaderValue($value);

        $new->syncHeaderLines($name);

        return $new;
    }

    public function withAddedHeader($name, $value)
    {
        if (!is_string($name) || method_exists($name, '__toString')) {
            throw new InvalidArgumentException(
                'header name must be a string or has __toString function when use withHeader function set a header'
            );
        }

        $name = trim((string) $name);
        $headerName = strtolower($name);
        $new = clone $this;

        if ($new->hasHeader($name)) {
            $header = $new->getHeader($name);
            if (is_array($value)) {
                foreach ($value as $eachValue) {
                    if (!in_array($eachValue, $header)) {
                        $new->headers[$headerName][] = trim($value);
                    }
                }
            } else {
                if (!in_array($value, $header)) {
                    $new->headers[$headerName][] = trim($value);
                }
            }
        } else {
            $new->headers[$headerName] = $new->normalizeHeaderValue($value);
        }

        $new->syncHeaderLines($name);

        return $new;
    }

    public function withoutHeader($name)
    {
        $new = clone $this;
        unset($new->headers[strtolower($name)]);
        $new->syncHeaderLines($name);
        return $new;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->body = $body;
        return $this->body;
    }

    protected function validateProtocol($protocol)
    {
        $validProtocol = ['1.1', '1.0', '2.0'];

        if (!in_array($protocol, $validProtocol)) {
            throw new InvalidArgumentException(sprintf(
                'protocol must be one of them, %s',
                implode(', ', $validProtocol)
            ));
        }
    }

    protected function normalizeHeaderValue($value)
    {
        if (is_array($value)) {
            foreach ($value as &$eachValue) {
                if (is_array($eachValue)) {
                    throw new InvalidArgumentException(
                        'header value must be an type can be convert to string'
                    );
                }
                $eachValue = trim($eachValue);
            }
            return $value;
        } else {
            return [trim($value)];
        }
    }

    protected function syncHeaderLines($name)
    {
        $headerName = strtolower($name);
        foreach (array_keys($this->headerLines) as $key) {
            if ($headerName === strtolower($key)) {
                unset($this->headers[$key]);
                break;
            }
        }

        if ($this->hasHeader($headerName)) {
            $this->headerLines[$name] = $this->headers[$headerName];
        }
    }

    protected function setHeaders(array $headers)
    {
        $this->headerLines = $this->headers = [];
        foreach ($headers as $name => $value) {
            $name = trim($name);
            $headerName = strtolower($name);
            if (!is_array($value)) {
                $value = trim($value);
                $this->headers[$headerName][] = $value;
                $this->headerLines[$name][] = $value;
            } else {
                foreach ($value as $eachValue) {
                    $eachValue = trim($eachValue);
                    $this->headers[$headerName][] = $eachValue;
                    $this->headerLines[$name][] = $eachValue;
                }
            }
        }
    }
}
