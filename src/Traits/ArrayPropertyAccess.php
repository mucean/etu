<?php

namespace Etu\Traits;

use InvalidArgumentException;

trait ArrayPropertyAccess
{
    protected $accessProperties = [];

    public function get($propertyName, array $accessPath = [], $default = null)
    {
        ;
    }

    public function set($propertyName, array $accessPath, $value)
    {
        ;
    }

    public function has($propertyName, array $accessPath)
    {
        ;
    }

    public function unset($propertyName, array $accessPath)
    {
        ;
    }

    protected function permissionValidate($propertyName)
    {
        if (!isset($this->accessProperties[$propertyName])) {
            throw new InvalidArgumentException(sprintf('%s is not register to access'));
        }
    }

    protected function registerPropertyAccess($propertyName, array $authority = [
        'visibility' => true,
        'modifyPermission' => false
    ])
    {
        if (!isset($this->$propertyName) && !is_array($this->$propertyName)) {
            throw new InvalidArgumentException('the property must be existed and an array type');
        }

        $defaultAuthority = [
            'visibility' => true,
            'modifyPermission' => false
        ];

        if (isset($authority['visibility']) && !(bool) $authority['visibility']) {
            $defaultAuthority['visibility'] = false;
        }

        if (isset($authority['modifyPermission']) && (bool) $authority['modifyPermission']) {
            $defaultAuthority['modifyPermission'] = true;
        }

        $this->accessProperties[$propertyName] = $defaultAuthority;
    }
}
