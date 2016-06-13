<?php
namespace Tests\Traits;

use Etu\Traits\Middleware;

/**
 * Class MiddlewareTest
 */
class MiddlewareTest extends \PHPUnit_Framework_TestCase
{
    use Middleware;

    protected $hello;

    /**
     * @before
     */
    public function testAddMiddleware()
    {
        $func1 = function () {
            $this->hello = '1';
            yield true;
            $this->hello .= ' 5';
        };
        $func1 = $func1->bindTo($this, $this);

        $func2 = function () {
            $this->hello .= ' 2';
            yield true;
            $this->hello .= ' 4';
        };
        $func2 = $func2->bindTo($this, $this);

        $func3 = function () {
            $this->hello .= ' 3';
        };
        $func3 = $func3->bindTo($this, $this);

        $this->addMiddleware($func1);

        $this->addMiddleware($func2);

        $this->addMiddleware($func3);
    }

    public function testExecuteMiddleware()
    {
        $this->executeMiddleware();

        $this->assertEquals($this->hello, '1 2 3 4 5');
    }
}
