<?php

namespace Etu;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Etu\Traits\EtuMiddleware as Middleware;
use Etu\Exception\NotFoundException;
use Closure;

class Router
{
    use Middleware;

    protected $basePath;

    protected $namespace;

    protected $rewrites = [];

    protected $container;

    public function __construct($basePath, $namespace = '\\', Container $container)
    {
        $this->basePath = trim($basePath, '/');
        $this->namespace = '\\' . trim($namespace, '\\');

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

    public function execute(RequestInterface $request, ResponseInterface $response)
    {
        return $this->executeMiddleware($request, $response);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $requestPath = $request->getUri()->getPath();
        $requestMethod = strtolower($request->getMethod());
        list($realPath, $arguments) = $this->rewrite($requestPath);
        if ($realPath === '/') {
            $realPath = '/index';
        }

        $mapClass = $this->mapClass($this->basePath . $realPath);
        if (!class_exists($mapClass) || !is_callable([$mapClass, $requestMethod])) {
            throw new NotFoundException('Request can not be processed', 404);
        }

        $controller = new $mapClass();

        $controller->request = $request;
        $controller->response = $response;

        $res = call_user_func_array([$controller, $requestMethod], $arguments);

        if ($res instanceof ResponseInterface) {
            $response = $res;
        } else {
            $response = $controller->response;
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

    protected function mapClass($requestPath)
    {
        $requestPath = trim($requestPath, '/');

        $eachPath = explode('/', $requestPath);

        array_walk($eachPath, function (&$item) {
            $item = ucfirst($item);
        });

        $separator = $this->namespace === '\\' ? '' : '\\';
        return $this->namespace . $separator . implode('\\', $eachPath);
    }
}
