<?php
namespace Etu\Interfaces;

interface PropertyAccessInterface
{
    // TODO 获取属性的解析方法(默认数组，可以自定义方法获取值)
    // TODO register那些属性可以使用这些方法获取值
    public function get($propertyName, array $accessPath, $default = null);
}
