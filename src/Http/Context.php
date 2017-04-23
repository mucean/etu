<?php
namespace Etu\Http;

use Etu\ArrayAccess;

class Context extends ArrayAccess
{
    public static function buildFromServer()
    {
        return new static($_SERVER);
    }

    public function keys()
    {
        return array_keys($this->all());
    }

    public function merge(array $data)
    {
        return $this->reset(array_merge($this->all(), $data));
    }
}
