<?php
namespace Etu\Http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

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
        $new->validateProtocol($version);
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
        if (!is_string($name) && !method_exists($name, '__toString')) {
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
        if (!is_string($name) && !method_exists($name, '__toString')) {
            throw new InvalidArgumentException(
                'header name must be a string or has __toString function when use withAddedHeader function'
            );
        }

        $name = trim((string) $name);
        $new = clone $this;

        $header = $new->getHeader($name);

        if ([] !== $header) {
            if (is_array($value)) {
                foreach ($value as $eachValue) {
                    if (!in_array($eachValue, $header)) {
                        $header[] = trim((string) $eachValue);
                    }
                }
            } else {
                if (!in_array($value, $header)) {
                    $header[] = trim((string) $value);
                }
            }
        } else {
            $header = $new->normalizeHeaderValue($value);
        }

        $new->headers[strtolower($name)] = $header;

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

        return $new;
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
                if (!is_string($eachValue) && !method_exists($eachValue, '__toString')) {
                    throw new InvalidArgumentException(
                        'header array value must only contains an type can be convert to string'
                    );
                }

                $eachValue = trim($eachValue);
            }

            return $value;
        } elseif (is_string($value) || method_exists($value, '__toString')) {
            return [trim($value)];
        } else {
            throw new InvalidArgumentException(
                'header value must be an type can be convert to string or an array contains string value'
            );
        }
    }

    protected function syncHeaderLines($name)
    {
        $headerName = strtolower($name);
        $isNew = true;

        foreach (array_keys($this->headerLines) as $key) {
            if (strtolower($key) === $headerName) {
                unset($this->headerLines[$key]);
                $isNew = false;
                break;
            }
        }

        if ($this->hasHeader($headerName)) {
            if ($isNew) {
                $key = $name;
            }

            $this->headerLines[$key] = $this->headers[$headerName];
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
