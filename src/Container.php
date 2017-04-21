<?php

namespace Etu;

use Etu\Interfaces\ContainerInterface;
use Etu\Traits\ArrayPropertyAllAccess;
use Closure;
use InvalidArgumentException;

class Container implements ContainerInterface
{
    use ArrayPropertyAllAccess;

    protected $container = [];

    protected $maintain = [];

    protected $calls = [];

    protected $defaultSetting = [];

    public function __construct(array $items = [])
    {
        $this->registerPropertyAccess('container', true);

        $this->registerPropertyAccess('maintain', true);

        $this->registerPropertyAccess('calls', true);

        $setting = $this->defaultSetting;
        if (isset($items['setting'])) {
            $setting = array_merge($setting, $items['setting']);
            unset($items['setting']);
        }

        $this->add(
            'setting',
            function () use ($setting) {
                return new ArrayAccess($setting);
            },
            false
        );

        foreach ($items as $item) {
            if (!is_array($item) || count($item) < 2) {
                throw new InvalidArgumentException(
                    'Container construct each item of array argument must be an array type and more than two element'
                );
            }
            call_user_func_array([$this, 'add'], $item);
        }
    }

    public function get($id, $arguments = [])
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        $value = $this->getProperty('container', $id);

        if (is_callable($value) && !$this->hasProperty('calls', $id)) {
            $call = $value;
            array_unshift($arguments, $this);
            $value = call_user_func_array($value, $arguments);
            if (!$this->hasProperty('maintain', $id)) {
                $this->setProperty('calls', $id, $call);
                $this->setProperty('container', $id, $value);
            }
        }

        return $value;
    }

    public function has($id)
    {
        return $this->hasProperty('container', $id);
    }

    public function add($id, $value, $bindThis = false)
    {
        if ($this->has($id)) {
            $this->remove($id);
        }

        if ($bindThis && is_callable($value) && ($value instanceof Closure)) {
            $value = $value->bindTo($this);
        }

        return $this->setProperty('container', $id, $value);
    }

    public function update($id, $value)
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        return $this->setProperty('container', $id, $value);
    }

    public function remove($id)
    {
        $this->unsetProperty('container', $id);
        $this->unsetProperty('maintain', $id);
        $this->unsetProperty('calls', $id);
    }

    public function getCalledCall($id)
    {
        if (!$this->hasProperty('calls', $id)) {
            throw new InvalidArgumentException(
                sprintf('Identifier %s is not found or not called', $id)
            );
        }

        return $this->getProperty('calls', $id);
    }

    public function maintain($id)
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Identifier %s is not found', $id));
        }

        if ($this->hasProperty('calls', $id)) {
            throw new InvalidArgumentException('service has been called, can not maintain');
        }

        $value = $this->getProperty('container', $id);
        if (!is_callable($value)) {
            throw new InvalidArgumentException('maintain service must be a callable function or object');
        }

        $this->setProperty('maintain', $id, true);
    }
}