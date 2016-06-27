<?php
namespace Etu;

use Etu\Traits\ArrayPropertyAllAccess;
use Countable;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

class ArrayAccess implements Countable, ArrayAccess, IteratorAggregate
{
    use ArrayPropertyAllAccess {
        ArrayPropertyAllAccess::get as protected getProperty;
        ArrayPropertyAllAccess::has as protected hasProperty;
        ArrayPropertyAllAccess::set as protected setProperty;
        ArrayPropertyAllAccess::unset as protected unsetProperty;
    }

    protected $value = [];

    public function __construct(array $value)
    {
        $this->registerPropertyAccess('value', true);

        foreach ($value as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function all()
    {
        return $this->value;
    }

    public function &get($key, $default = null)
    {
        return $this->getProperty('value', $this->getAccessKey($key), $default);
    }

    public function has($key)
    {
        return $this->hasProperty('value', $this->getAccessKey($key));
    }

    public function set($key, $value)
    {
        return $this->setProperty('value', $this->getAccessKey($key), $value);
    }

    public function unset($key)
    {
        return $this->unsetProperty('value', $this->getAccessKey($key));
    }

    public function &offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        return $this->unset($key);
    }

    public function count()
    {
        return count($this->value);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->value);
    }
}
