<?php
namespace Etu\Traits;

use InvalidArgumentException;

trait ArrayPropertyAllAccess
{
    use ArrayPropertyReadAccess;

    protected function setProperty($propertyName, $accessPath, $value)
    {
        $accessPath = $this->getAccessKey($accessPath);
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

    protected function unsetProperty($propertyName, $accessPath)
    {
        $accessPath = $this->getAccessKey($accessPath);
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
