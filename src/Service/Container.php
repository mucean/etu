<?php

namespace Etu\Service;

use Etu\Interfaces\ContainerInterface;
use Etu\Traits\Singleton;

class Container
{
    use Singleton;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * set a class instance that implements \Etu\Interfaces\ContainerInterface
     *
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * get a service by a name
     *
     * @param $name
     * @return mixed
     */
    public function getService($name)
    {
        return $this->getContainer()->get($name);
    }

    /**
     * set a service by a name
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function addService($name, $value)
    {
        $this->getContainer()->add($name, $value);
        return $this;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        if ($this->container === null) {
            throw new \RuntimeException('ContainerInterface instance is not set');
        }
        return $this->container;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getContainer(), $name], $arguments);
    }
}