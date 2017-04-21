<?php
namespace Tests;

use Etu\Container;
use Etu\DefaultServices;
use Etu\Interfaces\ContainerInterface;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $container = new Container();
        DefaultServices::register($container);
        $this->assertInstanceOf('Etu\Container', $container);
        return $container;
    }

    /**
     * @depends testConstruct
     */
    public function testGet(Container $container)
    {
        $this->assertInstanceOf('Etu\Http\Context', $container->get('context'));
        $this->assertInstanceOf('Etu\Http\Request', $container->get('request'));
        $this->assertInstanceOf('Etu\Http\Response', $container->get('response'));
    }

    /**
     * @depends testConstruct
     */
    public function testGetIfNotExist(Container $container)
    {
        $identify = 'hello';
        $this->expectExceptionMessage(sprintf('Identifier %s is not found', $identify));
        $container->get($identify);
    }

    /**
     * @depends testConstruct
     */
    public function testAdd(Container $container)
    {
        $identify = 'hi';
        $value = 'Hello, world!';
        $container->add($identify, $value);
        $this->assertEquals($value, $container->get($identify));
    }

    /**
     * @param $container ContainerInterface
     * @depends testConstruct
     */
    public function testAddClosure(ContainerInterface $container)
    {
        $context = $container->get('context');
        $identifytify = 'Closure';
        $value = function (ContainerInterface $container) {
            return $container->get('context');
        };
        $container->add($identifytify, $value);
        $this->assertEquals($context, $container->get($identifytify));
    }

    /**
     * @param $container ContainerInterface
     * @depends testConstruct
     */
    public function testRemove(ContainerInterface $container)
    {
        $identify = 'hi';
        $value = 'Hello, world!';
        $this->assertEquals($value, $container->get($identify));
        $container->remove($identify);
        $this->expectExceptionMessage(sprintf('Identifier %s is not found', $identify));
        $container->get($identify);
    }

    /**
     * @depends testConstruct
     */
    public function testGetCalledCall(Container $container)
    {
        $context = $container->get('context');
        $identify = 'Closure';
        $value = function (ContainerInterface $container) {
            return $container->get('context');
        };
        $container->add($identify, $value);
        $this->assertEquals($context, $container->get($identify));
        $this->assertInstanceOf('Closure', $container->getCalledCall($identify));
    }

    /**
     * @depends testConstruct
     */
    public function testMaintain(Container $container)
    {
        $context = $container->get('context');
        $identify = 'Closure';
        $value = function (ContainerInterface $container) {
            return $container->get('context');
        };
        $container->add($identify, $value);
        $container->maintain($identify);
        $this->assertEquals($context, $container->get($identify));
        $this->expectExceptionMessage(sprintf('Identifier %s is not found or not called', $identify));
        $container->getCalledCall($identify);
    }

    /**
     * @depends testConstruct
     */
    public function testCalledCanNotMaintain(Container $container)
    {
        $context = $container->get('context');
        $identify = 'Closure';
        $value = function (ContainerInterface $container) {
            return $container->get('context');
        };
        $container->add($identify, $value);
        $this->assertEquals($context, $container->get($identify));
        $this->expectExceptionMessage('service has been called, can not maintain');
        $container->maintain($identify);
    }

    /**
     * @depends testConstruct
     */
    public function testNotCallableCanNotMaintain(Container $container)
    {
        $identify = 'hi';
        $value = 'Hello, world!';
        $container->add($identify, $value);
        $this->expectExceptionMessage('maintain service must be a callable function or object');
        $container->maintain($identify);
    }

    /**
     * @depends testConstruct
     */
    public function testUpdate(Container $container)
    {
        $context = $container->get('context');
        $identify = 'Closure';
        $value = function (ContainerInterface $container) {
            return $container->get('context');
        };
        $container->add($identify, $value);
        $this->assertEquals($context, $container->get($identify));
        $updateValue = 'Hello, world!';
        $container->update($identify, $updateValue);
        $this->assertEquals($updateValue, $container->get($identify));
        $this->assertInstanceOf('Closure', $container->getCalledCall($identify));
    }

    /**
     * @depends testConstruct
     */
    public function testUpdateException(Container $container)
    {
        $identify = 'errorId';
        $this->expectExceptionMessage(sprintf('Identifier %s is not found', $identify));
        $container->update($identify, 'error');
    }
}
