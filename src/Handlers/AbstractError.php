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
        $text = sprintf('Error type: %s', get_class($error));

        if ($message = $error->getMessage()) {
            $text .= PHP_EOL . sprintf('    message: %s', $message);
        }

        if ($code = $error->getCode()) {
            $text .= PHP_EOL . sprintf('    code: %s', $code);
        }

        if ($file = $error->getFile()) {
            $text .= PHP_EOL . sprintf('    file: %s', $file);
        }

        if ($line = $error->getLine()) {
            $text .= PHP_EOL . sprintf('    line: %s', $line);
        }

        if ($trace = $error->getTraceAsString()) {
            $text .= PHP_EOL . sprintf('    trace: %s', $trace);
        }

        return $error->getMessage();
    }

    protected function writeToErrorLog($message)
    {
        error_log($message);
    }
}
