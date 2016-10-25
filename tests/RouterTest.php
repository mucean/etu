<?php
namespace Tests;

use Tests\Http\BuildContext;
use Etu\Http\Request;
use Etu\Http\Response;
use Etu\Router;
use Etu\Container;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected $request;
    protected $response;

    public function __construct()
    {
        $context = BuildContext::getContext([
            'REQUEST_URI' => '/',
            'REQUEST_METHOD' => 'GET'
        ]);
        $this->request = Request::buildFromContext($context);
        $this->response = new Response();
        parent::__construct();
    }

    public function testConstruct()
    {
        $router = new Router('/Router', '\\Tests', new Container());
        $this->assertInstanceOf('Etu\Router', $router);

        return $router;
    }

    /**
     * @depends testConstruct
     */
    public function testExecute(Router $router)
    {
        $response = $router->execute($this->request, $this->response);
        $this->assertInstanceOf('Etu\Http\Response', $response);
        $this->assertEquals('Hello, world!', (string) $response->getBody());
    }
}
