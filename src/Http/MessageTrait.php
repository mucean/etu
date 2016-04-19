<?php

namespace Etu\Http;

use Psr\Http\Message\StreamInterface;

trait MessageTrait
{
    private $protocol = '1.1';

    private $stram;

    protected $headers = [];

    protected $headerLines = [];

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version)
    {
        if ($this->protocol !== $version) {
            $this->protocol = $version;
        }
        return $this;
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
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                'header name must be a string when use withHeader function set a header'
            );
        }

        $name = trim($name);
        $headerName = strtolower($name);

        $this->headers[$headerName] = $this->normalizeHeaderValue($value);

        $this->syncHeaderLines($name);

        return $this;
    }

    public function withAddedHeader($name, $value)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                'header name must be a string when use withHeader function set a header'
            );
        }

        $name = trim($name);
        $headerName = strtolower($name);
        if ($this->hasHeader($name)) {
            if (is_array($value)) {
                foreach ($value as $eachValue) {
                    if (!in_array($eachValue, $this->getHeader($name))) {
                        $this->headers[$headerName][] = trim($value);
                    }
                }
            } else {
                if (!in_array($value, $this->getHeader($name))) {
                    $this->headers[$headerName][] = trim($value);
                }
            }
        } else {
            $this->headers[$headerName] = $this->normalizeHeaderValue($value);
        }

        $this->syncHeaderLines($name);

        return $this;
    }

    public function withoutHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return $this;
        }
        unset($this->headers[strtolower($name)]);
        $this->syncHeaderLines($name);
        return $this;
    }

    public function getBody()
    {
        // todo if empty return stream
        return $this->stream ?: '';
    }

    public function withBody(StreamInterface $body)
    {
        if ($body !== $this->stream) {
            $this->stream = $body;
        }
        return $this->stream;
    }

    protected function normalizeHeaderValue($value)
    {
        if (is_array($value)) {
            foreach ($value as &$eachValue) {
                if (is_array($eachValue)) {
                    throw new \InvalidArgumentException(
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
            }
        }

        if ($this->hasHeader($headerName)) {
            $this->headerLines[$name] = $this->headers[$headerName];
        }
    }

    private function setHeaders(array $headers)
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
