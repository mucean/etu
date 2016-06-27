<?php
namespace Etu\Http;

use Etu\ArrayAccess;

// class Context implements \ArrayAccess, \Countable
class Context extends ArrayAccess
{
    public static function buildFromServer()
    {
        return new static($_SERVER);
    }
    /* protected $context = [];

    public function __construct(array $context)
    {
        $this->context = $context;
    }

    public function all()
    {
        return $this->context;
    }

    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->context[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        $this->context[$key] = $value;
    } */

    public function keys()
    {
        // return array_keys($this->context);
        return array_keys($this->value);
    }

    /* public function has($key)
    {
        return isset($this->context[$key]);
    }

    public function remove($key)
    {
        unset($this->context[$key]);
    } */

    public function merge(array $data)
    {
        $this->value = array_merge($this->value, $data);
    }

    /* public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function &offsetGet($offset)
    {
        return $this->context[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    public function count()
    {
        return count($this->context);
    } */
}
