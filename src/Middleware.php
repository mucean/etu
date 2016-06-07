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

    protected $bindThis;

    public function __construct(Object $bindThis = null)
    {
        $this->bindThis = $bindThis;
    }

    /**
     * add middleware wate to exec
     *
     * @param  callable $middleware
     * @return null
     */
    public function add(callable $middleware, $isBind = false)
    {
        if ($isBind && ($middleware instanceof \Closure)) {
            $middleware = $middleware->bindTo($this->bindThis);
        }

        $this->middlewares[] = $middleware;
    }

    /**
     * execute added middleware sequently
     *
     * @return null
     */
    public function execute($arguments = [])
    {
        $nextExecs = [];

        foreach ($this->middlewares as $middleware) {
            $res = call_user_func_array($middleware, $arguments);

            if ($res instanceof \Generator) {
                $nextExecs[] = $res;
            }
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
    public function reset()
    {
        $this->middlewares = [];
    }
}
