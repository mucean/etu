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

    abstract protected function doDelete(Data $data, Service $service = null);

    protected function __beforeUpdate() {}
    protected function __afterUpdate() {}
    protected function __beforeInsert() {}
    protected function __afterInsert() {}
    protected function __beforeDelete() {}
    protected function __afterDelete() {}
    protected function __beforeSave() {}
    protected function __afterSave() {}

    /**
     * according to primary key get the entity
     * @param $primaryValues
     * @return \Etu\ORM\Data | null
     */
    public function find($primaryValues)
    {
        return $this->doFind($primaryValues);
    }

    /**
     * update entity date
     * @param Data $data
     * @param Service|null $service
     * @return bool
     */
    public function update(Data $data, Service $service = null)
    {
        $this->__beforeUpdate();
        $this->doUpdate($data, $service);
        $data->__pack([], true);
        $this->__afterUpdate();
        return true;
    }

    /**
     * create new entity
     * @param Data $data
     * @param Service|null $service
     * @return bool
     */
    public function insert(Data $data, Service $service = null)
    {
        $this->__beforeInsert();

        $ids = $this->doInsert($data, $service);

        $data->__pack($ids, true);

        $this->__afterInsert();

        return true;
    }

    /**
     * delete record from service
     * @param Data $data
     * @param Service|null $service
     * @return bool
     */
    public function delete(Data $data, Service $service = null)
    {
        $this->__beforeDelete();
        $this->doDelete($data, $service);
        $this->__afterDelete();
        return true;
    }

    /**
     * insert or update record
     * @param Data $data
     * @param Service|null $service
     * @return bool
     */
    public function save(Data $data, Service $service = null)
    {
        $this->__beforeSave();
        if ($data->isNew()) {
            $this->insert($data, $service);
        } else {
            $this->update($data, $service);
        }
        $this->__afterSave();

        return true;
    }

    /**
     * init mapper class
     */
    protected function init()
    {
        $className = $this->className;
        list($config, $attributes) = $className::getOptions();
        $this->config = array_merge($this->config, $config);
        $this->verify();

        foreach ($attributes as $key => &$attribute) {
            $this->normalizeAttribute($attribute);
            if ($attribute['primaryKey']) {
                $this->config['primaryKeys'][] = $key;
            }
        }

        $this->attributes = $attributes;
    }

    protected function getAttributeType($name)
    {
        return array_key_exists($name, $this->attributes[$name])
            ? $this->attributes['type']
            : null;
    }

    /**
     * normalize data attribute
     * @param array $attribute
     */
    protected function normalizeAttribute(array &$attribute)
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

    protected function verify()
    {
        $this->verifyConfig('service');
    }

    /**
     * verify the config attribute if it is true
     * @param string $name
     * @throws Exception\ParameterNotRight
     */
    protected function verifyConfig($name)
    {
        if (array_key_exists($name, $this->config) === false || empty($this->config[$name])) {
            throw new Exception\ParameterNotRight(
                sprintf('%s config does not set or empty', $name)
            );
        }
    }
}