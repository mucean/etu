<?php

namespace Etu;

use Etu\Middleware;
use Etu\Container;

class Router
{
    protected $middleware;

    public function __construct()
    {
        $this->middleware = new Middleware(Container::getInstance());
    }

    public function execute()
    {
    }
}
