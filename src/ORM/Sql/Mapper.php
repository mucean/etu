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

    public function __construct(array $config, $className)
    {
        $this->config = array_merge($this->config, $config);
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

    public function select()
    {
        $select = new Select($this->getService(), $this->config['table']);
        $select->setWrapper(function ($data) {
            $entity = new $this->className;
            return $entity->pack($data);
        });

        return $select;
    }
}