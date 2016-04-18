<?php

namespace Etu\Http;

use Psr\Http\Message\StreamInterface;

trait MessageTrait
{
    private $protocol = '1.1';

    private $stram;

    protected $headers = [];

    protected $header_lines = [];

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
        return $this->header_lines;
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
        $header_name = strtolower($name);

        $this->headers[$header_name] = $this->normalizeHeaderValue($value);

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
        $header_name = strtolower($name);
        if ($this->hasHeader($name)) {
            if (is_array($value)) {
                foreach ($value as $each_value) {
                    if (!in_array($each_value, $this->getHeader($name))) {
                        $this->headers[$header_name][] = trim($value);
                    }
                }
            } else {
                if (!in_array($value, $this->getHeader($name))) {
                    $this->headers[$header_name][] = trim($value);
                }
            }
        } else {
            $this->headers[$header_name] = $this->normalizeHeaderValue($value);
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
            foreach ($value as &$each_value) {
                if (is_array($each_value)) {
                    throw new \InvalidArgumentException(
                        'header value must be an type can be convert to string'
                    );
                }
                $each_value = trim($each_value);
            }
            return $value;
        } else {
            return [trim($value)];
        }
    }

    protected function syncHeaderLines($name)
    {
        $header_name = strtolower($name);
        foreach (array_keys($this->header_lines) as $key) {
            if ($header_name === strtolower($key)) {
                unset($this->headers[$key]);
            }
        }

        if ($this->hasHeader($header_name)) {
            $this->header_lines[$name] = $this->headers[$header_name];
        }
    }

    private function setHeaders(array $headers)
    {
        $this->header_lines = $this->headers = [];
        foreach ($headers as $name => $value) {
            $name = trim($name);
            $header_name = strtolower($name);
            if (!is_array($value)) {
                $value = trim($value);
                $this->headers[$header_name][] = $value;
                $this->header_lines[$name][] = $value;
            } else {
                foreach ($value as $each_value) {
                    $each_value = trim($each_value);
                    $this->headers[$header_name][] = $each_value;
                    $this->headerLines[$name][] = $each_value;
                }
            }
        }
    }
}
