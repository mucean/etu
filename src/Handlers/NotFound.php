<?php
namespace Etu\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Etu\Stream;

/**
 * Class NotFound
 */
class NotFound extends AbstractThrowable
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $contentType = $this->responseContentType($request);

        $renderMethodName = isset($this->knownContentType[$contentType])
            ? $this->knownContentType[$contentType]
            : 'renderHtmlError';

        if (!method_exists($this, $renderMethodName)) {
            throw new RuntimeException(sprintf('%s method do not exists', $renderMethodName));
        }

        $message = $this->$renderMethodName();

        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($message);

        return $response
            ->withBody($body)
            ->withStatus(404)
            ->withHeader('Content-type', $contentType);
    }

    protected function renderHtmlError()
    {
        $title = 'Not Found';

        $body = sprintf('<h1>%s</h1>', $title);

        return sprintf(
            '<html><head><title>%s</title></head><body>%s</body></html>',
            $title,
            $body
        );
    }

    protected function renderJsonError()
    {
        return json_encode(['message' => 'Not Found'], JSON_PRETTY_PRINT);
    }

    protected function renderXmlError()
    {
        return '<root><message>Not Found</message></root>';
    }
}
