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
            $this->hello = 'hello';
            yield true;
            $this->hello .= ', world';
        };
        $func1 = $func1->bindTo($this, $this);

        $func2 = function () {
            $this->hello .= ', mucean';
        };
        $func2 = $func2->bindTo($this, $this);

        $this->addMiddleware($func1);

        $this->addMiddleware($func2);
    }

    public function testExecuteMiddleware()
    {
        $this->executeMiddleware();

        $this->assertEquals($this->hello, 'hello, mucean, world');
    }
}
