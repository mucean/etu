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

        if (count($params) < 2) {
            throw new InvalidArgumentException('arguments passed to EtuMiddle must more than two');
        }

        if (!$params[0] instanceof ServerRequestInterface) {
            throw new InvalidArgumentException(
                'first argument passed to EtuMiddle must instanceof Psr\Http\Message\ServerRequestInterface'
            );
        }

        if (!$params[1] instanceof ResponseInterface) {
            throw new InvalidArgumentException(
                'second argument passed to EtuMiddle must instanceof Psr\Http\Message\ResponseInterface'
            );
        }

        return true;
    }

    protected function returnValidate($return)
    {
        if (!$return instanceof ResponseInterface) {
            throw new UnexpectedValueException(
                'value of middleware returned must instance of \Psr\Http\Message\ResponseInterface'
            );
        }

        return true;
    }
}
