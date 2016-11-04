<?php

namespace Etu\ORM\Type;

class Json extends Common
{
    public function store($value)
    {
        $value = json_encode($value, JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $value;
    }

    public function restore($value)
    {
        $value = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $value;
    }
}