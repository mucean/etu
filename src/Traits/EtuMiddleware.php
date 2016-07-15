<?php

namespace Etu\Traits;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/**
 * Trait EtuMiddleware
 */
trait EtuMiddleware
{
    use Middleware;

    protected function paramsValidate()
    {
        $params = func_get_args();

        if (!($params[0] instanceof ServerRequestInterface)) {
            throw new InvalidArgumentException('Etu middleware first param must instanceof ServerRequestInterface');
        }

        if (!($params[1] instanceof ResponseInterface)) {
            throw new InvalidArgumentException('Etu middleware second param must instanceof ResponseInterface');
        }

        return true;
    }

    protected function returnValidate($returnValue)
    {
        if (!($returnValue instanceof ResponseInterface)) {
            throw new UnexpectedValueException('Etu middleware return value must instanceof ResponseInterface');
        }

        return true;
    }
}
