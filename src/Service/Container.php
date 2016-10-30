<?php

namespace Etu\Service;

use Etu\Container as BaseContainer;
use Etu\Traits\Singleton;

class Container
{
    use Singleton;

    protected $container;

    protected function __construct(BaseContainer $container = null)
    {
        if ($container === null) {
            $container = new BaseContainer();
        }

        $this->container = $container;
    }

    public function getService($name)
    {
        return $this->container->get($name);
    }

    public function addService($name, $value, $bindTo = true)
    {
        $this->container->add($name, $value, $bindTo);
        return $this;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }
}