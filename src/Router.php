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
        $this->setKernel($this);

        $this->executeMiddleware(
            $this->container->get('request'),
            $this->container->get('response')
        );
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $requestPath = $request->getUri()->getPath();
    }
}
