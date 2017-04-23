<?php
namespace Tests\Traits;

use Etu\DefaultServices;
use Etu\Traits\EtuMiddleware as Middleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Etu\Container;

/**
 * Class MiddlewareTest
 */
class MiddlewareTest extends \PHPUnit\Framework\TestCase
{
    use Middleware;

    public function testExecuteMiddleware()
    {
        $this->addMiddleware(function ($request, $response, $next) {
            $response->write('2');
            $response = $next($request, $response);
            $response->write('4');
            return $response;
        });

        $this->addMiddleware(function ($request, $response, $next) {
            $response->write('1');
            $response = $next($request, $response);
            $response->write('5');
            return $response;
        });

        $container = new Container();
        DefaultServices::register($container);
        $response = $this->executeMiddleware($container->get('request'), $container->get('response'));

        $this->assertEquals('12345', (string) $response->getBody());
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $response->write('3');
        return $response;
    }
}
