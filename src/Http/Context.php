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
        return array_keys($this->values);
    }

    public function merge(array $data)
    {
        $this->values = array_merge($this->values, $data);
    }
}
