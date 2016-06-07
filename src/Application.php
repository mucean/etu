<?php
namespace Etu;

use Etu\Http\Context;
use Etu\Http\Request;
use Etu\Http\Response;
use Etu\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Etu\Container;

class Application
{
    /**
     * handle exception if $this->start()
     *
     * @var callable
     */
    protected $exceptionHandler = null;

    /**
     * middleware class
     *
     * @var \Etu\Middleware
     */
    protected $middleware;

    protected $container;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->middleware = new Middleware($this->container);
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

        return $response;
    }

    public function process(ServerRequestInterface $request, Response $response)
    {
        $request;

        return $response;
    }

    public function addMiddleware(callable $middleware)
    {
        $this->middleware->add($middleware);
    }

    /**
     * setExceptionHandler
     * @return null
     * @author mucean
     **/
    public function setExceptionHandler(callable $handler)
    {
        $this->exceptionHandler = $handler;
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
