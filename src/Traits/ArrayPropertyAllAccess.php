<?php
namespace Etu\Traits;

use InvalidArgumentException;

trait ArrayPropertyAllAccess
{
    use ArrayPropertyReadAccess;

    public function set($propertyName, array $accessPath, $value)
    {
        $this->permissionValidate($propertyName, ['isWriteOperate' => true, 'throwException' => true]);

        if ([] === $accessPath) {
            throw new InvalidArgumentException('accessPath must not be a empty array');
        }

        $data = &$this->$propertyName;

        foreach ($accessPath as $path) {
            if (!isset($data[$path])) {
                $data[$path] = [];
            }

            $data = &$data[$path];
        }

        $data = $value;

        return $this;
    }

    function unset($propertyName, array $accessPath)
    {
        $this->permissionValidate($propertyName, ['isWriteOperate' => true, 'throwException' => true]);

        if ([] === $accessPath) {
            throw new InvalidArgumentException('accessPath must not be a empty array');
        }

        $data = &$this->$propertyName;
        $lastKey = array_pop($accessPath);

        foreach ($accessPath as $path) {
            if (!isset($data[$path])) {
                return true;
            }

            $data = &$data[$path];
        }

        if (isset($data[$lastKey])) {
            unset($data[$lastKey]);
        }

        return true;
    }
}
