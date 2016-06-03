<?php
namespace Etu;

use Etu\Http\Context;
use Etu\Http\Request;
use Etu\Http\Response;
use Etu\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
    protected $middleware = null;

    public function __construct()
    {
        $this->middleware = new Middleware();
    }

    /**
     * run app handle request
     *
     * @return ResponseInterface
     */
    public function run()
    {
        $context = new Context($_SERVER);
        $request = Request::buildFromContext($context);
        $response = new Response();

        try {
            $this->middlewar->execute();
        } catch (\Exception $e) {
            $handler = $this->exceptionHandler;

            if (null === $handler) {
                $handler = function () use ($request, $response) {
                    // todo handle exception
                };
            }

            call_user_func_array($handler, [$e]);
        }
    }

    public function process(ServerRequestInterface $request, Response $response)
    {
        $request;

        return $response;
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
