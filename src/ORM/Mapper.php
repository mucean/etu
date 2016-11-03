<?php

namespace Etu\ORM;

use Etu\Service;

/**
 * Class Mapper
 * @author mucean
 */
abstract class Mapper
{
    /**
     * @var \Etu\Service\Sql
     */
    protected $service;

    /**
     * the class name of entity
     * @var \Etu\ORM\Data
     */
    protected $className;

    protected $config = [
        'service' => ''
    ];

    /**
     * data attribute
     * @var array
     */
    protected $attributes;

    public function __construct($className)
    {
        $this->className = $className;
        $this->init();
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

        return $this->service = $this->initService();
    }

    abstract protected function initService();

    abstract protected function doFind($primaryValues, Service $service = null);

    abstract protected function doUpdate(Data $data, Service $service = null);

    abstract protected function doInsert(Data $data, Service $service = null);

    abstract protected function deDelete(Data $data, Service $service = null);

    /**
     * @param $primaryValues
     * @return \Etu\ORM\Data | null
     */
    public function find($primaryValues)
    {
        return $this->doFind($primaryValues);
    }

    /**
     * init mapper class
     */
    protected function init()
    {
        $className = $this->className;
        list($config, $attributes) = $className::getOptions();
        $this->config = array_merge($this->config, $config);

        foreach ($attributes as $key => &$attribute) {
            $this->normalizeAttribute($attribute);
            if ($attribute['primaryKey']) {
                $this->config['primaryKeys'][] = $key;
            }
        }

        $this->attributes = $attributes;
    }

    /**
     * normalize data attribute
     * @param array $attribute
     */
    public function normalizeAttribute(array &$attribute)
    {
        $defaultAttribute = [
            'type' => null,
            'refuseUpdate' => false,
            'allowNull' => false,
            'primaryKey' => false,
        ];

        $type = array_key_exists('type', $attribute) ? $attribute['type'] : null;

        $attribute = array_merge(
            $defaultAttribute,
            Type::factory($type)->normalizeAttribute($attribute)
        );

        if (array_key_exists('default', $attribute) === false && $attribute['allowNull']) {
            $attribute['default'] = null;
        }

        if ($attribute['primaryKey']) {
            $attribute['allowNull'] = false;
            $attribute['refuseUpdate'] = true;
        }
    }
}