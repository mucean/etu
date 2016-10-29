<?php
namespace Tests;

use Etu\Container;
use Etu\DefaultServices;

class ContainerTest extends \PHPUnit_Framework_TestCase
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
        $iden = 'hello';
        $this->setExpectedException('InvalidArgumentException', sprintf('Identifier %s is not found', $iden));
        $container->get($iden);
    }

    /**
     * @depends testConstruct
     */
    public function testAdd(Container $container)
    {
        $iden = 'hi';
        $value = 'Hello, world!';
        $container->add($iden, $value);
        $this->assertEquals($value, $container->get($iden));
    }

    /**
     * @depends testConstruct
     */
    public function testAddClosure(Container $container)
    {
        $context = $container->get('context');
        $iden = 'Closure';
        $value = function () {
            return $this->get('context');
        };
        $container->add($iden, $value);
        $this->assertEquals($context, $container->get($iden));
    }

    /**
     * @depends testConstruct
     */
    public function testRemove(Container $container)
    {
        $iden = 'hi';
        $value = 'Hello, world!';
        $this->assertEquals($value, $container->get($iden));
        $container->remove($iden);
        $this->setExpectedException('InvalidArgumentException', sprintf('Identifier %s is not found', $iden));
        $container->get($iden);
    }

    /**
     * @depends testConstruct
     */
    public function testGetCalledCall(Container $container)
    {
        $context = $container->get('context');
        $iden = 'Closure';
        $value = function () {
            return $this->get('context');
        };
        $container->add($iden, $value);
        $this->assertEquals($context, $container->get($iden));
        $this->assertInstanceOf('Closure', $container->getCalledCall($iden));
    }

    /**
     * @depends testConstruct
     */
    public function testMaintain(Container $container)
    {
        $context = $container->get('context');
        $iden = 'Closure';
        $value = function () {
            return $this->get('context');
        };
        $container->add($iden, $value);
        $container->maintain($iden);
        $this->assertEquals($context, $container->get($iden));
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('Identifier %s is not found or not called', $iden)
        );
        $container->getCalledCall($iden);
    }

    /**
     * @depends testConstruct
     */
    public function testCalledCanNotMaintain(Container $container)
    {
        $context = $container->get('context');
        $iden = 'Closure';
        $value = function () {
            return $this->get('context');
        };
        $container->add($iden, $value);
        $this->assertEquals($context, $container->get($iden));
        $this->setExpectedException(
            'InvalidArgumentException',
            'service has been called, can not maintain'
        );
        $container->maintain($iden);
    }

    /**
     * @depends testConstruct
     */
    public function testNotCallableCanNotMaintain(Container $container)
    {
        $iden = 'hi';
        $value = 'Hello, world!';
        $container->add($iden, $value);
        $this->setExpectedException(
            'InvalidArgumentException',
            'maintain service must be a callable function or object'
        );
        $container->maintain($iden);
    }

    /**
     * @depends testConstruct
     */
    public function testUpdate(Container $container)
    {
        $context = $container->get('context');
        $iden = 'Closure';
        $value = function () {
            return $this->get('context');
        };
        $container->add($iden, $value);
        $this->assertEquals($context, $container->get($iden));
        $updateValue = 'Hello, world!';
        $container->update($iden, $updateValue);
        $this->assertEquals($updateValue, $container->get($iden));
        $this->assertInstanceOf('Closure', $container->getCalledCall($iden));
    }

    /**
     * @depends testConstruct
     */
    public function testUpdateException(Container $container)
    {
        $iden = 'errorId';
        $this->setExpectedException(
            'InvalidArgumentException',
            sprintf('Identifier %s is not found', $iden)
        );
        $container->update($iden, 'error');
    }
}
