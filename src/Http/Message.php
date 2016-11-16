<?php
namespace Etu\Http;

use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    protected $protocol = '1.1';

    /**
     * @var Headers
     */
    protected $headers;

    /**
     * @var StreamInterface
     */
    protected $body;

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
        return $this->headers->all();
    }

    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    public function getHeader($name)
    {
        return $this->headers->get($name);
    }

    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader($name, $value)
    {
        $new = clone $this;

        $new->headers->set($name, $value);

        return $new;
    }

    public function withAddedHeader($name, $value)
    {
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
            $header = $value;
        }

        $new->headers->set($name, $header);

        return $new;
    }

    public function withoutHeader($name)
    {
        $new = clone $this;

        $new->headers->unset($name);

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
}
