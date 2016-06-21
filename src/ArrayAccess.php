<?php
namespace Etu;

use Etu\Traits\ArrayPropertyAllAccess;

class ArrayAccess
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
        $this->value = $value;

        $this->registerPropertyAccess('value', true);
    }

    public function get($key, $default = null)
    {
        return $this->getProperty('value', [$key], $default);
    }

    public function has($key)
    {
        return $this->hasProperty('value', [$key]);
    }

    public function set($key, $value)
    {
        return $this->setProperty('value', [$key], $value);
    }

    public function unset($key)
    {
        return $this->unsetProperty('value', [$key]);
    }
}
