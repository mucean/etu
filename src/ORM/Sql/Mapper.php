<?php

namespace Etu\ORM\Sql;

use Etu\ORM\Mapper as BaseMapper;
use Etu\ORM\Type;
use Etu\ORM\Data;
use Etu\Service;
use Etu\Service\Container;

class Mapper extends BaseMapper
{
    protected $config = [
        'service' => '',
        'table' => '',
        'primaryKeys' => []
    ];

    /**
     * init service from config
     * @return \Etu\Service\Sql
     */
    protected function initService()
    {
        return $this->service = Container::getInstance()->getService($this->config['service']);
    }

    /**
     * get sql command class, array of data entity will return when execute command
     * @param Service $service
     * @return Select
     */
    public function select(Service $service = null)
    {
        if ($service === null) {
            $service = $this->getService();
        }

        $select = new Select($service, $this->config['table']);
        $select->setWrapper(function ($data) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $this->attributes) === false) {
                    continue;
                }
                $data[$key] = Type::factory($this->attributes[$key]['type'])->restore($value);
            }
            /** @var $entity \Etu\ORM\Data */
            $entity = new $this->className();
            return $entity->__pack($data);
        });

        return $select;
    }

    protected function doFind($primaryValues, Service $service = null)
    {
        if ($service === null) {
            $service = $this->getService();
        }

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

        $select = $this->select($service);
        foreach ($this->config['primaryKeys'] as $key => $primaryKey) {
            $select->where(sprintf('%s = ?', $primaryKey), $primaryValues[$key]);
        }

        $data = $select->get();

        return count($data) === 0 ? null : $data[0];
    }

    protected function doUpdate(Data $data, Service $service = null)
    {
    }

    protected function doInsert(Data $data, Service $service = null)
    {
    }

    protected function doDelete(Data $data, Service $service = null)
    {
    }
}