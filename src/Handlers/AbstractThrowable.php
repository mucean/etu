<?php
namespace Etu\Handlers;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractThrowable
{
    protected $knownContentType = [
        'text/html' => 'renderHtmlError',
        'application/json' => 'renderJsonError',
        'application/xml' => 'renderXmlError',
        'text/xml' => 'renderXmlError'
    ];

    /**
     * determine which we known content type for response
     *
     * @return string
     */
    public function responseContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $knownContentType = array_keys($this->knownContentType);
        $acceptedContentType = array_intersect(
            $knownContentType,
            explode(',', $acceptHeader)
        );

        $contentType = 'text/html';

        if (isset($acceptedContentType[0])) {
            $contentType = $acceptedContentType[0];
        }

        // handle with +json or +xml accept
        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $pregType = sprintf('application/%s', $matches[1]);
            if (in_array($pregType, $knownContentType)) {
                $contentType = $pregType;
            }
        }

        return $contentType;
    }
}
