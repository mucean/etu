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
     * @var \Etu\ORM\Data
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

    protected function doFind($primaryValues)
    {
        if (is_array($primaryValues) === false) {
            $primaryValues = [$primaryValues];
        }

        if (count($primaryValues) !== count($this->config['primaryKeys'])) {
            throw new \InvalidArgumentException(sprintf(
                'there is %d primary key, %d primary value gave',
                count($this->config['primaryKeys']),
                count($primaryValues)
            ));
        }

        $select = $this->select();
        foreach ($this->config['primaryKeys'] as $key => $primaryKey) {
            $select->where(sprintf('%s = ?', $primaryKey), $primaryValues[$key]);
        }

        $data = $select->get();

        return count($data) === 0 ? null : $data[0];
    }
}