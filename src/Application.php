<?php
namespace Etu;

use Etu\Http\Context;
use Etu\Http\Request;
use Etu\Http\Response;
use Etu\Traits\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Etu\Container;

class Application
{
    use Middleware;

    /**
     * handle exception if $this->start()
     *
     * @var callable
     */
    protected $exceptionHandler = null;

    protected $container;

    public function __construct($container = [])
    {
        if (is_array($container)) {
            $container = Container::getInstance($container);
        }

        if (!($container instanceof Container)) {
            throw new InvalidArgumentException('A container expected');
        }

        $this->container = $container;
    }

    /**
     * run app handle request
     *
     * @return ResponseInterface
     */
    public function run()
    {
        $request = $this->container->get('request');
        $response = $this->container->get('response');

        $response = $this->process($request, $response);

        $this->response($response);
    }

    public function process(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $this->executeMiddleware($request, $response);
        } catch (\Throwable $error) {
            $response = $this->handlerError($error, $request, $response);
        }

        return $response;
    }

    public function response(ResponseInterface $response)
    {
        echo $response->getBody();
    }

    public function add(callable $middleware)
    {
        if ($middleware instanceof \Closure) {
            $middleware = $middleware->bindTo($this);
        }

        $this->addMiddleware($middleware);
        return $this;
    }

    /**
     * setExceptionHandler
     * @return null
     * @author mucean
     **/
    public function setExceptionHandler(callable $handler)
    {
        if ($handler instanceof \Closure) {
            $handler = $handler->bindTo($this);
        }

        $this->exceptionHandler = $handler;
    }

    // public function handleException(\Exception $excep, ServerRequestInterface $request, Response $response)
    // {
    // }

    public function handlerError(\Throwable $error, ServerRequestInterface $request, ResponseInterface $response)
    {
        $handler = 'errorHandler';

        if (!$this->container->has($handler)) {
            throw $error;
        }

        $parameters = [$error, $request, $response];

        return call_user_func_array($this->container->get($handler), $parameters);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $router = $this->container->get('router');

        return $router->execute($request, $response);
    }

    /**
     * Register a namespace bind directory for auto load class
     *
     * @param string   $dir       The directory
     * @param string   $namespace The classname
     * @param callable $func      callable functiontion of user defined
     *
     * @return null
     */
    public static function registerNamespace($dir, $namespace, callable $func = null)
    {
        $dir = rtrim(strval($dir), '\\/');

        if (TEST) {
            if (!is_dir($dir)) {
                throw new \Exception(
                    sprintf('invalid directory was given: %s', $dir)
                );
            }
        }

        if (null === $func) {
            $preNamespace = ltrim($namespace, '\\');
            $preNamespaceLen = strlen($preNamespace);
            $func = function ($class) use (
                $preNamespace,
                $dir,
                $preNamespaceLen
            ) {
                $class = ltrim($class, '\\');

                if ('' === $preNamespace) {
                    $partDir = str_replace('\\', '/', $class);
                } else {
                    $validateResult = strpos($class, $preNamespace);

                    if (false === $validateResult || $validateResult > 0) {
                        return;
                    }

                    $partDir = str_replace(
                        '\\',
                        DIRECTORY_SEPARATOR,
                        substr($class, $preNamespaceLen + 1)
                    );
                }

                $file = $dir . DIRECTORY_SEPARATOR . $partDir . '.php';

                if (is_file($file)) {
                    include_once $file;
                }
            };
        }

        spl_autoload_register($func);
    }
}
