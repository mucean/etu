<?php
namespace Etu\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

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

        return $response;
    }

    protected function renderHtmlError($error)
    {
        return $error;
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
