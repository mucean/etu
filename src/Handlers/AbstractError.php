<?php
namespace Etu\Handlers;

abstract class AbstractError extends AbstractThrowable
{
    protected $showErrorDetails;

    public function __construct($showErrorDetails = false)
    {
        $this->showErrorDetails = (bool) $showErrorDetails;
    }

    protected function logError(\Throwable $error)
    {
        if ($this->showErrorDetails) {
            return null;
        }

        $message = $this->getErrorMessage($error);
        while ($error = $error->getPrevious()) {
            $message .= PHP_EOL . 'Previous Error:' . PHP_EOL;
            $message .= $this->getErrorMessage($error);
        }

        $this->writeToErrorLog($message);
    }

    protected function getErrorMessage(\Throwable $error)
    {
        $text = PHP_EOL . sprintf('Error type: %s', get_class($error));

        if ($message = $error->getMessage()) {
            $text .= PHP_EOL . sprintf('Message: %s', $message);
        }

        if ($code = $error->getCode()) {
            $text .= PHP_EOL . sprintf('Code: %s', $code);
        }

        if ($file = $error->getFile()) {
            $text .= PHP_EOL . sprintf('File: %s', $file);
        }

        if ($line = $error->getLine()) {
            $text .= PHP_EOL . sprintf('Line: %s', $line);
        }

        if ($trace = $error->getTraceAsString()) {
            $text .= PHP_EOL . sprintf('Trace: %s' . PHP_EOL, $trace);
        }

        return $text;
    }

    protected function writeToErrorLog($message)
    {
        error_log($message);
    }
}
