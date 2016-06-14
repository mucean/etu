<?php
namespace Etu\Traits;

use RuntimeException;
use UnexpectedValueException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * a middleware package
 *
 * @package middleware
 * @subpackage none
 * @author mucean
 */
trait Middleware
{
    /**
     * middlewares of wating to exec
     *
     * @var array
     */
    protected $middlewares = [];

    protected $locked = false;

    /**
     * add middleware wate to exec
     *
     * @param  callable $middleware
     * @return null
     */
    protected function addMiddleware(callable $middleware)
    {
        if ($this->locked) {
            throw new RuntimeException('can not add when middleware is execute');
        }

        if ($this->middlewares === []) {
            $this->prepareMiddlewares();
        }

        $next = $this->middlewares[0];

        array_unshift(
            $this->middlewares,
            function (RequestInterface $request, ResponseInterface $response) use ($middleware, $next) {
                $result = call_user_func($middleware, $request, $response, $next);

                if (!$result instanceof ResponseInterface) {
                    throw new UnexpectedValueException(
                        'value of middleware returned must instance of \Psr\Http\Message\ResponseInterface'
                    );
                }

                return $result;
            }
        );

        return $this;
    }

    protected function prepareMiddlewares(callable $kernel = null)
    {
        if ($this->middlewares !== []) {
            throw new RuntimeException('prepare middleware can only be called once');
        }

        if ($kernel === null) {
            $kernel = $this;
        }

        $this->middlewares[] = $kernel;
    }

    /**
     * execute added middleware sequently
     *
     * @return null
     */
    public function executeMiddleware(RequestInterface $request, ResponseInterface $response)
    {
        if ($this->middlewares === []) {
            $this->prepareMiddlewares();
        }

        $this->locked = true;
        $result = $this->middlewares[0]($request, $response);
        $this->locked = false;

        return $result;
    }

    /**
     * reset middlewares
     *
     * @return null
     */
    protected function resetMiddleware()
    {
        $this->middlewares = [];
    }
}
