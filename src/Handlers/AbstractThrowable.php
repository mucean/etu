<?php
namespace Etu\Handlers;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractThrowable
{
    protected $knownContentType = [
        'text/html',
        'application/json',
        'application/xml',
        'text/xml'
    ];

    /**
     * determine which we known content type for response
     *
     * @return string
     */
    public function responseContentType(ServerRequestInterface $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $acceptedContentType = array_intersect(
            $this->knownContentType,
            explode(',', $acceptHeader)
        );

        $contentType = 'text/html';

        if (isset($acceptedContentType[0])) {
            $contentType = $acceptedContentType[0];
        }

        // handle with +json or +xml accept
        if (preg_match('/\+(json|xml)/', $acceptHeader, $matches)) {
            $pregType = sprintf('application/%s', $matches[1]);
            if (in_array($pregType, $this->knownContentType)) {
                $contentType = $pregType;
            }
        }

        return $contentType;
    }
}
