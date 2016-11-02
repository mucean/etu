<?php

namespace Etu\ORM\Sql;

use Etu\ORM\Mapper as BaseMapper;
use Etu\Service\Container;

class Mapper extends BaseMapper
{
    /**
     * @var \Etu\Service\Sql
     */
    protected $service;

    /**
     * the class name of entity
     * @var string
     */
    protected $className;

    protected $config = [
        'service' => '',
        'table' => ''
    ];

    /**
     * data attribute
     * @var array
     */
    protected $attributes;

    public function __construct($className)
    {
        list($config, $attributes) = $className::getOptions();
        $this->config = array_merge($this->config, $config);
        $this->attributes = $attributes;
        $this->className = $className;
    }

    /**
     * get service from config
     * @return \Etu\Service\Sql
     */
    public function getService()
    {
        if ($this->service !== null) {
            return $this->service;
        }

        return $this->service = Container::getInstance()->getService($this->config['service']);
    }

    public function find($primaryId)
    {
    }

    /**
     * get sql command class, array of data entity will return when execute command
     * @return Select
     */
    public function select()
    {
        $select = new Select($this->getService(), $this->config['table']);
        $select->setWrapper(function ($data) {
            /** @var $entity \Etu\ORM\Data */
            $entity = new $this->className();
            return $entity->pack($data);
        });

        return $select;
    }

    public function normalizeAttribute(array $attributes)
    {
    }
}