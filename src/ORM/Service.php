<?php

namespace Etu\ORM;

use Etu\Container;
use Etu\Traits\Singleton;

class Service
{
    use Singleton;

    protected $container;

    protected function __construct(Container $container = null)
    {
        if ($container === null) {
            $container = new Container();
        }

        $this->container = $container;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }
}