<?php
namespace Etu;

use Etu\Traits\ArrayPropertyAllAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;

class ArrayAccess implements Countable, \ArrayAccess, IteratorAggregate
{
    use ArrayPropertyAllAccess;

    protected $values = [];

    public function __construct(array $values = [])
    {
        $this->registerPropertyAccess('values', true);

        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * reset the array
     *
     * @param array $values
     * @return $this
     */
    public function reset(array $values = [])
    {
        $this->values = [];
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    /**
     * set a array to the array
     * @param array $values
     * @return $this
     */
    public function setArray(array $values)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    public function all()
    {
        return $this->values;
    }

    public function &get($key, $default = null)
    {
        return $this->getProperty('values', $key, $default);
    }

    public function has($key)
    {
        return $this->hasProperty('values', $key);
    }

    public function set($key, $value)
    {
        return $this->setProperty('values', $key, $value);
    }

    public function unset($key)
    {
        return $this->unsetProperty('values', $key);
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
        return count($this->values);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->values);
    }
}
