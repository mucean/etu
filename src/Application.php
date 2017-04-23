<?php
namespace Etu;

use Etu\Interfaces\ContainerInterface;
use Etu\Traits\EtuMiddleware as Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Etu\Exception\NotFoundException;

class Application
{
    use Middleware;

    /**
     * Container
     * @var ContainerInterface
     */
    protected $container;

    protected $defaultSetting = [
        'showErrorDetails' => false,
        'addContentLengthHeader' => true,
    ];

    public function __construct($container = [])
    {
        if (is_array($container)) {
            $setting = $this->defaultSetting;
            if (isset($container['setting'])) {
                $setting = array_merge($setting, $container['setting']);
                unset($container['setting']);
            }

            $container = new Container($container);
            DefaultServices::register($container);
            $container->get('setting')->setArray($setting);
        }

        if (!($container instanceof ContainerInterface)) {
            throw new \InvalidArgumentException('A class instance expected implement ContainerInterface');
        }

        $this->container = $container;
    }

    /**
     * enable to custom container
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * run app handle request
     *
     * @param $silent bool
     * @return ResponseInterface
     */
    public function run($silent = false)
    {
        $request = $this->container->get('request');
        $response = $this->container->get('response');

        $response = $this->process($request, $response);

        if ($silent === false) {
            $this->respond($response);
        }

        return $response;
    }

    /**
     * process the request
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $response = $this->executeMiddleware($request, $response);
        } catch (\Exception $e) {
            $response = $this->handleException($e, $request, $response);
        } catch (\Throwable $error) {
            $response = $this->handleError($error, $request, $response);
        }

        $response = $this->finalize($response);

        return $response;
    }

    /**
     * finalize the response
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function finalize(ResponseInterface $response)
    {
        ini_set('default_mimetype', '');

        if ($this->isEmptyResponse($response)) {
            return $response->withoutHeader('Content-Type')->withoutHeader('Content-Length');
        }

        if ($this->container->get('setting')->get('addContentLengthHeader')) {
            if (ob_get_length() > 0) {
                throw new \RuntimeException('Output buffer has unexpected data');
            }
            $length = $response->getBody()->getSize();
            if ($length !== null && !$response->hasHeader('Content-Length')) {
                return $response->withHeader('Content-Length', strval($length));
            }
        }

        return $response;
    }

    public function respond(ResponseInterface $response)
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

        if ($this->isEmptyResponse($response)) {
        }
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

    public function handleException(\Exception $exp, ServerRequestInterface $request, ResponseInterface $response)
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

    public function handleError(\Throwable $error, ServerRequestInterface $request, ResponseInterface $response)
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

    public function isEmptyResponse(ResponseInterface $response)
    {
        if ($this->container->has('emptyResponseCheck')) {
            return $this->container->get('emptyResponseCheck')($response);
        }

        return in_array($response->getStatusCode(), [204, 302, 304]);
    }

    /**
     * Register a namespace bind directory for auto load class
     *
     * @param string   $dir       The directory
     * @param string   $namespace The class name
     * @param callable $func      callable function of user defined
     * @throws
     *
     * @return void
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
