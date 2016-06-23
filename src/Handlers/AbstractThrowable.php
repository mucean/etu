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
            explode(',', $acceptHeader),
            $knownContentType
        );

        $contentType = 'text/html';

        if (isset($acceptedContentType[0])) {
            $contentType = $acceptedContentType[0];
        } elseif (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) { // handle with +json or +xml accept
            $pregType = sprintf('application/%s', $matches[1]);
            if (in_array($pregType, $knownContentType)) {
                $contentType = $pregType;
            }
        }

        return $contentType;
    }
}
