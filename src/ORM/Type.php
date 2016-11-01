<?php

namespace Etu\ORM;

use Etu\Container;
use Etu\Traits\Singleton;

class Type
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

    public function get($name)
    {
        return $this->container->get($name);
    }

    public function add($name, $value, $bindTo = true)
    {
        $this->container->add($name, $value, $bindTo);
        return $this;
    }

    /**
     * factory type class
     * @param string $name
     * @return Type\Common
     */
    public static function factory($name)
    {
        return static::getInstance()->get($name);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }
}