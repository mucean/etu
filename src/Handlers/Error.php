<?php
namespace Etu\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Etu\Stream;

class Error extends AbstractError
{
    public function __invoke(
        \Error $error,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $contentType = $this->responseContentType($request);

        switch ($contentType) {
            case 'application/json':
                $message = $this->renderJsonError($error);
                break;
            
            case 'text/xml':
            case 'application/xml':
                $message = $this->renderXmlError($error);
                break;
            
            default:
                $message = $this->renderHtmlError($error);
                break;
        }

        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write($message);

        return $response->withBody($body);
    }

    protected function renderHtmlError($error)
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
            $text .= '<br>' . sprintf('trace: %s', $trace);
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
