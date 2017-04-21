<?php

namespace Etu\Interfaces;

interface ContainerInterface
{
    /**
     * obtain the contents of the container by name
     * @param $name string
     * @return mixed
     */
    public function get($name);

    /**
     * check if the container has the contents by name
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * add a new content to the container
     * @param $name
     * @param $value
     * @return static
     */
    public function add($name, $value);

    /**
     * remove it from container
     * @param $name
     * @return static
     */
    public function remove($name);
}
