<?php
namespace Etu\Http;

use Etu\ArrayAccess;
use Etu\Http\Context;
use Etu\Interfaces\Http\HeadersInterface;

/**
 * Class Headers
 */
class Headers extends ArrayAccess implements HeadersInterface
{
    public static function buildFromContext(Context $context)
    {
        $headers = [];
        foreach ($context as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[$key] = $value;
            }
        }

        return new static($headers);
    }

    public function all()
    {
        $all = parent::all();

        $headers = [];
        foreach ($all as $value) {
            $headers[$value['originalKey']] = $value['value'];
        }

        return $headers;
    }

    public function &get($key)
    {
        $key = $this->getAccessKey($key);

        return parent::get($key)['value'];
    }

    public function has($key)
    {
        return parent::has($this->getAccessKey($key));
    }

    public function unset($key)
    {
        return parent::unset($this->getAccessKey($key));
    }

    public function set($originalKey, $value)
    {
        $key = $this->getAccessKey($originalKey);

        return parent::set($key, [
            'value' => $value,
            'originalKey' => $originalKey
        ]);
    }

    public function getAccessKey($key)
    {
        $key = strtolower(str_replace('_', '-', $key));

        if (strpos($key, 'HTTP_') === 0) {
            $key = substr($key, 5);
        }

        return $key;
    }
}
