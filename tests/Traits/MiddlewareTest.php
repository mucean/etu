<?php
namespace Tests\Traits;

use Etu\Traits\EtuMiddleware as Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Etu\Container;

/**
 * Class MiddlewareTest
 */
class MiddlewareTest extends \PHPUnit_Framework_TestCase
{
    use Middleware;

    public function testExecuteMiddleware()
    {
        $this->addMiddleware(function ($request, $response, $next) {
            $response->write('2');
            $response = $next($request, $response);
            return $response->write('4');
        });

        $this->addMiddleware(function ($request, $response, $next) {
            $response->write('1');
            $response = $next($request, $response);
            return $response->write('5');
        });

        $container = Container::getInstance();
        $response = $this->executeMiddleware($container->get('request'), $container->get('response'));

        $this->assertEquals('12345', (string) $response->getBody());
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        return $response->write('3');
    }
}
