<?php
namespace Etu;

use Etu\Traits\EtuMiddleware as Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Etu\Exception\NotFoundException;
use InvalidArgumentException;
use Closure;
use Exception;
use Throwable;

class Application
{
    use Middleware;

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
    public function run($silent = false)
    {
        $request = $this->container->get('request');
        $response = $this->container->get('response');

        $response = $this->process($request, $response);

        if (!$silent) {
            $this->response($response);
        }

        return $response;
    }

    public function process(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $this->executeMiddleware($request, $response);
        } catch (Exception $e) {
            $response = $this->handleException($e, $request, $response);
        } catch (Throwable $error) {
            $response = $this->handleError($error, $request, $response);
        }

        return $response;
    }

    public function response(ResponseInterface $response)
    {
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders() as $key => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $key, $value), false);
                }
            }
        }
        echo $response->getBody();
    }

    public function add(callable $middleware)
    {
        if ($middleware instanceof Closure) {
            $middleware = $middleware->bindTo($this);
        }

        $this->addMiddleware($middleware);
        return $this;
    }

    public function handleException(Exception $exp, ServerRequestInterface $request, ResponseInterface $response)
    {
        $handler = '';
        if ($exp instanceof NotFoundException) {
            $handler = 'notFoundHandler';
        }

        if ($handler === '') {
            $handler = 'userDefinedHandler';
        }

        if (!$this->container->has($handler)) {
            throw $exp;
        }

        $parameters = [$request, $response, $exp];

        return call_user_func_array($this->container->get($handler), $parameters);
    }

    public function handleError(Throwable $error, ServerRequestInterface $request, ResponseInterface $response)
    {
        $handler = 'errorHandler';

        if (!$this->container->has($handler)) {
            throw $error;
        }

        $parameters = [$request, $response, $error];

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
                throw new Exception(
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
