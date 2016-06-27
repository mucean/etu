<?php
namespace Etu\Http;

use Etu\ArrayAccess;
use Etu\Interfaces\Http\HeadersInterface;
use InvalidArgumentException;
use ArrayIterator;

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

    public function &get($key, $default = [])
    {
        $key = $this->getHeaderName($key);

        $header = parent::get($key);
        if ($header === null) {
            return $default;
        }

        return $header['value'];
    }

    public function has($key)
    {
        return parent::has($this->getHeaderName($key));
    }

    public function unset($key)
    {
        return parent::unset($this->getHeaderName($key));
    }

    public function set($originalKey, $value)
    {
        $key = $this->getHeaderName($originalKey);

        return parent::set($key, [
            'value' => $this->normalizeHeaderValue($value),
            'originalKey' => $originalKey
        ]);
    }

    public function getIterator()
    {
        $data = $this->all();

        return new ArrayIterator($data);
    }

    public function getHeaderName($key)
    {
        $key = strtolower(str_replace('_', '-', $key));

        if (strpos($key, 'http-') === 0) {
            $key = substr($key, 5);
        }

        return $key;
    }

    protected function normalizeHeaderValue($value)
    {
        if (is_array($value)) {
            foreach ($value as &$eachValue) {
                if (!is_string($eachValue) && !method_exists($eachValue, '__toString')) {
                    throw new InvalidArgumentException(
                        'header array value must only contains an type can be convert to string'
                    );
                }

                $eachValue = trim($eachValue);
            }

            return $value;
        } elseif (is_string($value) || method_exists($value, '__toString')) {
            return [trim($value)];
        } else {
            throw new InvalidArgumentException(
                'header value must be an type can be convert to string or an array contains string value'
            );
        }
    }
}
