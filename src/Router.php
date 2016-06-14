<?php

namespace Etu;

use Etu\Traits\Middleware;
use Etu\Container;
use RuntimeException;
use Closure;

class Router
{
    use Middleware;

    protected $basePath;

    protected $namespace;

    protected $rewrites = [];

    protected $container;

    public function __construct($basePath, $namespace = '\\', Container $container = null)
    {
        $this->basePath = trim($basePath, '/');
        $this->namespace = '\\' . trim($namespace, '\\');

        if ($container === null) {
            $container = Container::getInstance();
        }

        $this->container = $container;
    }

    public function add(callable $middleware)
    {
        if ($middleware instanceof Closure) {
            $middleware = $middleware->bindTo($this->container);
        }

        $this->addMiddleware($middleware);

        return $this;
    }

    public function withRewrite(array $rewrites)
    {
        $this->rewrites = $rewrites;
        return $this;
    }

    public function withAddedRewrite(array $rewrites)
    {
        $this->rewrites = array_merge($this->rewrites, $rewrites);
        return $this;
    }

    public function execute()
    {
        return $this->executeMiddleware(
            $this->container->get('request'),
            $this->container->get('response')
        );
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $requestPath = $request->getUri()->getPath();
        $requestMethod = strtolower($request->getMethod());

        list($realPath, $arguments) = $this->rewrite($requestPath);
        $mapClass = $this->namespace . '\\' . str_replace('/', '\\', $this->basePath . $realPath);
        if (!class_exists($mapClass)) {
            throw new RuntimeException();
        }

        $controller = new $mapClass();

        if (!is_callable([$controller, $requestMethod])) {
            throw new RuntimeException();
        }

        $res= call_user_func_array([$controller, $requestMethod], $arguments);

        if ($res instanceof ResponseInterface) {
            $response = $res;
        } else {
            $response = $this->container->get('response');
        }

        return $response;
    }

    protected function rewrite($requestPath)
    {
        $realPath = $requestPath;
        $arguments = [];

        foreach ($this->rewrites as $pattern => $value) {
            if (preg_match($pattern, $requestPath, $matches)) {
                $realPath = $value;
                $arguments = array_pop($matches);
                break;
            }
        }

        return [$realPath, $arguments];
    }
}
