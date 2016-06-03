<?php
namespace Etu\Http;

use Etu\Stream;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterface
{
    protected $statusCode = 200;

    protected $reasonPhrase;

    protected static $validCodeAndReasonMap = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    public function __construct($statusCode = 200, array $headers = [], StreamInterface $body = null)
    {
        $this->statusCode = $statusCode;
        $this->setHeaders($headers);
        $this->body = $body ? $body : new Stream(fopen('php://temp', 'w+'));
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->validateStatusCode($code);
        $new = clone $this;
        $new->statusCode = $code;

        if ($reasonPhrase) {
            $new->reasonPhrase = $reasonPhrase;
        } else {
            $new->reasonPhrase = $new->getDefaultReasonPhrase($code);
        }

        return $new;
    }

    public function getReasonPhrase()
    {
        if (null !== $this->reasonPhrase) {
            return $this->reasonPhrase;
        }

        $statusCode = $this->getStatusCode();

        return $this->reasonPhrase = $this->getDefaultReasonPhrase($statusCode);
    }

    public function write($data)
    {
        $this->getBody()->write($data);

        return $this;
    }

    public function redirect($url, $statusCode = 302)
    {
        $new = $this->withHeader('Location', (string) $url);

        if ($new->getStatusCode() === 200) {
            return $new->withStatus($statusCode);
        }

        return $new;
    }

    protected function validateStatusCode($statusCode)
    {
        if (!is_int($statusCode) || $statusCode < 100 || $statusCode > 599) {
            throw new InvalidArgumentException('Invalid status code');
        }
    }

    protected function getDefaultReasonPhrase($statusCode)
    {
        if (isset(static::$validCodeAndReasonMap[$statusCode])) {
            return static::$validCodeAndReasonMap[$statusCode];
        } else {
            return '';
        }
    }
}
