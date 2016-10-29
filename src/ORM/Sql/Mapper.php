<?php

namespace Etu\ORM\Sql;

use \Etu\ORM\Mapper as BaseMapper;

class Mapper extends BaseMapper
{
    /**
     * @var \Etu\Service\Sql
     */
    protected $service;

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
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

        return $this->service;
    }

    public function find($primaryId)
    {
    }

    public function select()
    {
    }
}
