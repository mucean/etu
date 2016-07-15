<?php
namespace Etu\Traits;

use RuntimeException;

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
     * middleware of waiting to exec
     *
     * @var array
     */
    protected $middleware = [];

    protected $locked = false;

    /**
     * add middleware waite to exec
     *
     * @param  callable $middleware
     * @return null
     */
    protected function addMiddleware(callable $middleware)
    {
        if ($this->locked) {
            throw new RuntimeException('can not add when middleware is execute');
        }

        if ($this->middleware === []) {
            $this->prepareMiddleware();
        }

        $next = $this->middleware[0];

        array_unshift(
            $this->middleware,
            $this->getAddedMiddleware($middleware, $next)
        );

        return $this;
    }

    protected function getAddedMiddleware(callable $middleware, callable $next)
    {
        return function () use ($middleware, $next) {
            $params = func_get_args();

            call_user_func_array([$this, 'paramsValidate'], $params);

            $params = array_merge($params, [$next]);
            $result = call_user_func_array($middleware, $params);

            call_user_func([$this, 'returnValidate'], $result);

            return $result;
        };
    }

    protected function prepareMiddleware(callable $kernel = null)
    {
        if ($this->middleware !== []) {
            throw new RuntimeException('prepare middleware can only be called once');
        }

        if ($kernel === null) {
            $kernel = $this;
        }

        $this->middleware[] = $kernel;
    }

    /**
     * execute added middleware sequent
     *
     * @return null
     */
    public function executeMiddleware()
    {
        if ($this->middleware === []) {
            $this->prepareMiddleware();
        }

        $this->locked = true;
        $params = func_get_args();
        call_user_func_array([$this, 'paramsValidate'], $params);
        $result = call_user_func_array($this->middleware[0], $params);
        $this->locked = false;

        return $result;
    }

    protected function paramsValidate()
    {
        return true;
    }

    protected function returnValidate($returnValue)
    {
        return true;
    }

    /**
     * reset middleware
     *
     * @return null
     */
    protected function resetMiddleware()
    {
        $this->middleware = [];
    }
}
