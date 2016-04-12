<?php

namespace Etu;

class Application
{
    /**
     * handle exception if $this->start()
     *
     * @var callable
     */
    protected $exception_handler = null;

    /**
     * middleware class
     *
     * @var \Etu\Middleware
     */
    protected $middleware = null;

    public function __construct()
    {
        $this->middleware = new \Etu\Middleware;
    }
    
    /**
     * Start app handle request
     *
     * @return null
     */
    public function start()
    {
        // todo complete Request and Response
        try {
            $this->middlewar->execute();
        } catch (\Exception $e) {
            $handler = $this->exception_handler;
            if ($handler === null) {
                $handler = function () use ($request, $response) {
                    // todo handle exception
                };
            }
            call_user_func_array($handler, [$e]);
        }
    }

    /**
     * setExceptionHandler
     * @return null
     * @author mucean
     **/
    public function setExceptionHandler(callable $handler)
    {
        $this->exception_handler = $handler;
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

        if ($func === null) {
            $pre_namespace = ltrim($namespace, '\\');
            $pre_namespace_len = strlen($pre_namespace);
            $func = function ($class) use (
                $pre_namespace,
                $dir,
                $pre_namespace_len
            ) {
                $class = ltrim($class, '\\');
                if ($pre_namespace === '') {
                    $part_dir = str_replace('\\', '/', $class);
                } else {
                    $validate_result = strpos($class, $pre_namespace);
                    if ($validate_result === false || $validate_result > 0) {
                        return null;
                    }
                    $part_dir = str_replace(
                        '\\',
                        DIRECTORY_SEPARATOR,
                        substr($class, $pre_namespace_len + 1)
                    );
                }
                $file = $dir . DIRECTORY_SEPARATOR . $part_dir . '.php';
                if (is_file($file)) {
                    include_once $file;
                }
            };
        }

        spl_autoload_register($func);
    }
}
