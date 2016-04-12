<?php

namespace Etu;

/**
 * a middleware package
 *
 * @package middleware
 * @subpackage none
 * @author mucean
 */
class Middleware
{
    /**
     * middlewares of wating to exec
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * add middleware wate to exec
     *
     * @param  callable $middleware
     * @return null
     */
    public function insert(callable $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * execute added middleware sequently
     *
     * @return null
     */
    public function execute()
    {
        $after_execs = [];
        foreach ($this->middlewares as $middleware) {
            $res = call_user_func_array($middleware, []);
            if ($res instanceof \Generator) {
                $after_execs[] = $res;
            }
        }

        while (($handle = array_pop($after_execs)) !== null) {
            $handle->next();
        }
    }

    /**
     * reset middlewares
     *
     * @return null
     */
    public function reset()
    {
        $this->middlewares = [];
    }
}
