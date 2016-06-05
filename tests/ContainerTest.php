<?php
namespace Tests;

use Etu\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $container = Container::getInstance();
        $this->assertInstanceOf('Etu\Container', $container);
        return $container;
    }
}
