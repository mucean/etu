<?php
namespace Etu\Handlers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Etu\Stream;
use Throwable;

class Error extends AbstractError
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Throwable $error
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
        $title = 'An error occurred';

        $body = sprintf('<h1>%s</h1>', $title);
        if ($this->showErrorDetails) {
            $body .= '<h2>Details</h2>';
            $body .= $this->renderHtml($error);

            while ($previous = $error->getPrevious()) {
                $body .= sprintf(
                    '<h2>Previous:</h2>%s',
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
        $text = sprintf('<div><b>Error type :</b> %s</div>', get_class($error));

        if ($message = $error->getMessage()) {
            $text .= sprintf('<div><b>Message :</b> %s</div>', $message);
        }

        if ($code = $error->getCode()) {
            $text .= sprintf('<div><b>Code :</b> %s</div>', $code);
        }

        if ($file = $error->getFile()) {
            $text .= sprintf('<div><b>File :</b> %s</div>', $file);
        }

        if ($line = $error->getLine()) {
            $text .= sprintf('<div><b>Line :</b> %s</div>', $line);
        }

        if ($trace = $error->getTraceAsString()) {
            $text .= sprintf('<div><b>Trace :</b> <br><pre>%s</pre></div>', $trace);
        }

        return $text;
    }

    protected function renderJsonError($error)
    {
        $text = ['Error' => 'An error occurred'];

        if ($this->showErrorDetails) {
            $text['details'] = [];

            do {
                $text['details'][] = [
                    'type' => get_class($error),
                    'message' => $error->getMessage(),
                    'code' => $error->getCode(),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                    'trace' => $error->getTraceAsString()
                ];
            } while ($error = $error->getPrevious());
        }

        return json_encode($text, JSON_PRETTY_PRINT);
    }

    protected function renderXmlError($error)
    {
        $text = "<error>\n  <message>An error occurred</message>\n";

        if ($this->showErrorDetails) {
            do {
                $text .= "  <details>\n";
                $text .= "    <type>" . get_class($error) . "</type>\n";
                $text .= "    <message>" . $error->getMessage() . "</message>\n";
                $text .= "    <code>" . $error->getCode() . "</code>\n";
                $text .= "    <file>" . $error->getFile() . "</file>\n";
                $text .= "    <line>" . $error->getLine() . "</line>\n";
                $text .= "    <trace>" . $error->getTraceAsString() . "</trace>\n";
                $text .= "  </details>\n";
            } while ($error = $error->getPrevious());
        }

        $text .= "</error>";

        return $text;
    }
}
