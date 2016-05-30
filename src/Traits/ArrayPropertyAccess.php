<?php

namespace Etu\Traits;

use InvalidArgumentException;
use RuntimeException;

trait ArrayPropertyAccess
{
    protected $accessProperties = [];

    public function get($propertyName, array $accessPath = [], $default = null)
    {
        if ($this->permissionValidate($propertyName) !== 0) {
            return $default;
        }

        $data = &$this->$propertyName;

        foreach ($accessPath as $path) {
            if (isset($data[$path])) {
                $data = &$data[$path];
            } else {
                return $default;
            }
        }

        return $data;
    }

    public function set($propertyName, array $accessPath, $value)
    {
        $this->permissionValidate($propertyName, ['isWriteOperate' => true, 'throwException' => true]);

        if ($accessPath === []) {
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
    }

    public function has($propertyName, array $accessPath)
    {
        if ($this->permissionValidate($propertyName) !== 0) {
            return false;
        }

        $data = &$this->$propertyName;

        foreach ($accessPath as $path) {
            if (isset($data[$path])) {
                $data = &$data[$path];
            } else {
                return false;
            }
        }

        return true;
    }

    public function unset($propertyName, array $accessPath)
    {
        $this->permissionValidate($propertyName, ['isWriteOperate' => true, 'throwException' => true]);

        if ($accessPath === []) {
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

    protected function permissionValidate(
        $propertyName,
        $option = ['isWriteOperate' => false, 'throwException' => false]
    ) {
        $isWriteOperate = $throwException = false;
        if (isset($option['isWriteOperate']) && (bool) $option['isWriteOperate']) {
            $isWriteOperate = true;
        }

        if (isset($option['throwException']) && (bool) $option['throwException']) {
            $throwException = true;
        }

        if (!isset($this->accessProperties[$propertyName])) {
            if ($throwException) {
                throw new InvalidArgumentException(sprintf('%s is not register to access', $propertyName));
            } else {
                return 1;
            }
        }

        if ($isWriteOperate && $isWriteOperate !== $this->accessProperties[$propertyName]) {
            if ($throwException) {
                throw new RuntimeException(sprintf('%s property is not allow to modify', $propertyName));
            } else {
                return 2;
            }
        }

        if ($throwException) {
            return true;
        } else {
            return 0;
        }
    }

    protected function registerPropertyAccess($propertyName, $modifyPermission = false)
    {
        if (!isset($this->$propertyName) || !is_array($this->$propertyName)) {
            throw new InvalidArgumentException('the property must be existed and an array type');
        }

        $this->accessProperties[$propertyName] = (bool) $modifyPermission;
    }
}
