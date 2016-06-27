<?php

namespace Etu\Interfaces\Http;

/**
 * Interface HeadersInterface
 * @author yourname
 */
interface HeadersInterface
{
    public function all();

    public function getHeaderName($key);

    public function get($key);

    public function set($key, $value);

    public function has($key);

    public function unset($key);
}
