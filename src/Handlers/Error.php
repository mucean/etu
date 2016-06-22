<?php
namespace Etu\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Etu\Stream;

class Error extends AbstractError
{
    public function __invoke(
        \Throwable $error,
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

        $message = $this->$renderMethodName($error);

        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($message);

        $this->logError($error);

        return $response
            ->withBody($body)
            ->withStatus(500)
            ->withHeader('Content-type', $contentType);
    }

    protected function renderHtmlError($error)
    {
        $title = 'A error has occured<br>';

        if ($this->showErrorDetails) {
            $body = '<h2>Details</h2>';
            $body .= $this->renderHtml($error);

            while ($previous = $error->getPrevious()) {
                $body .= sprintf(
                    '<div>Previous:</div>%s',
                    $previous
                );
            }
        }

        return sprintf(
            '<html><head><title>%s</title></head><body>%s</body></html>',
            $title,
            $body
        );
    }

    protected function renderHtml($error)
    {
        $text = sprintf('Error type: %s', get_class($error));

        if ($message = $error->getMessage()) {
            $text .= '<br>' . sprintf('message: %s', $message);
        }

        if ($code = $error->getCode()) {
            $text .= '<br>' . sprintf('code: %s', $code);
        }

        if ($file = $error->getFile()) {
            $text .= '<br>' . sprintf('file: %s', $file);
        }

        if ($line = $error->getLine()) {
            $text .= '<br>' . sprintf('line: %s', $line);
        }

        if ($trace = $error->getTraceAsString()) {
            $text .= sprintf('<br>trace: <br><pre>%s</pre>', $trace);
        }

        return $text;
    }

    protected function renderJsonError($error)
    {
        return $error;
    }

    protected function renderXmlError($error)
    {
        return $error;
    }
}
