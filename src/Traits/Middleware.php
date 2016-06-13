<?php
namespace Etu\Traits;

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

    protected $kernel;

    /**
     * add middleware wate to exec
     *
     * @param  callable $middleware
     * @return null
     */
    protected function addMiddleware(callable $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    protected function setKernel(callable $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * execute added middleware sequently
     *
     * @return null
     */
    public function executeMiddleware($arguments = [])
    {
        $nextExecs = [];

        foreach ($this->middlewares as $middleware) {
            $res = call_user_func_array($middleware, $arguments);

            if ($res instanceof \Generator && $res->current()) {
                $nextExecs[] = $res;
            }
        }

        if ($this->kernel !== null) {
            call_user_func_array($this->kernel, $arguments);
        }

        while (($handle = array_pop($nextExecs)) !== null) {
            $handle->next();
        }
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
