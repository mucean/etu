<?php

namespace Etu;

use Etu\Traits\Middleware;
use Etu\Container;

class Router
{
    use Middleware;

    protected $container;

    public function __construct(Container $container = null)
    {
        if ($container === null) {
            $container = Container::getInstance();
        }

        $this->container = $container;
    }

    public function execute()
    {
    }
}
