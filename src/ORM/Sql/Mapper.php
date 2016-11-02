<?php

namespace Etu\ORM\Sql;

use Etu\ORM\Mapper as BaseMapper;
use Etu\ORM\Type;
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
        'table' => '',
        'primaryKeys' => []
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

        return $this->service = Container::getInstance()->getService($this->config['service']);
    }

    /**
     * @param $primaryId
     * @return \Etu\ORM\Data | null
     */
    public function find($primaryId)
    {
        if (is_array($primaryId) === false) {
            $primaryId = [$primaryId];
        }

        $select = $this->select();
        foreach ($this->config['primaryKeys'] as $key => $primaryKey) {
            $select->where(sprintf('%s = ?', $primaryKey), $primaryId[$key]);
        }

        $data = $select->get();

        return count($data) === 0 ? null : $data[0];
    }

    /**
     * get sql command class, array of data entity will return when execute command
     * @return Select
     */
    public function select()
    {
        $select = new Select($this->getService(), $this->config['table']);
        $select->setWrapper(function ($data) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $this->attributes) === false) {
                    continue;
                }
                $data[$key] = Type::factory($this->attributes[$key]['type'])->restore($value);
            }
            /** @var $entity \Etu\ORM\Data */
            $entity = new $this->className();
            return $entity->pack($data);
        });

        return $select;
    }

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

    public function normalizeAttribute(array &$attribute)
    {
        $defaultAttribute = [
            'type' => null,
            'refuseUpdate' => false,
            'allow_null' => false,
            'primaryKey' => false,
        ];

        $type = array_key_exists('type', $attribute) ? $attribute['type'] : null;

        $attribute = array_merge(
            $defaultAttribute,
            Type::factory($type)->normalizeAttribute($attribute)
        );

        if (array_key_exists('default', $attribute) === false && $attribute['allow_null']) {
            $attribute['default'] = null;
        }

        if ($attribute['primaryKey']) {
            $attribute['allow_null'] = false;
            $attribute['refuseUpdate'] = true;
        }
    }
}