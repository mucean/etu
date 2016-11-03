<?php

namespace Etu\ORM\Sql;

use Etu\ORM;
use Etu\Service;

class Mapper extends ORM\Mapper
{
    protected $config = [
        'service' => '',
        'connection' => '',
        'primaryKeys' => []
    ];

    /**
     * init service from config
     * @return \Etu\Service\Sql
     */
    protected function initService()
    {
        return $this->service = Service\Container::getInstance()->getService($this->config['service']);
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

        $select = new Select($service, $this->getConnection());
        $select->setWrapper(function ($data) {
            foreach ($data as $key => $value) {
                if (array_key_exists($key, $this->attributes) === false) {
                    continue;
                }
                $data[$key] = ORM\Type::factory($this->attributes[$key]['type'])->restore($value);
            }
            /** @var $entity \Etu\ORM\Data */
            $entity = new $this->className();
            return $entity->__pack($data);
        });

        return $select;
    }

    /**
     * get connection of service
     * @return string
     */
    public function getConnection()
    {
        return $this->config['connection'];
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

    protected function doUpdate(ORM\Data $data, Service $service = null)
    {
        if ($service === null) {
            $service = $this->service;
        }

        $update = $service->update($this->getConnection());

        foreach ($this->config['primaryKeys'] as $primaryKey) {
            $update->where(sprintf('%s = ?', $primaryKey), $data->get($primaryKey));
        }

        foreach ($data->getModifiedAttributes() as $attribute) {
            $update->set(
                sprintf('%s = ?', $attribute),
                ORM\Type::factory($this->getAttributeType($attribute))->restore($data->get($attribute))
            );
        }

        return $update->execute();
    }

    protected function doInsert(ORM\Data $data, Service $service = null)
    {
        if ($service === null) {
            $service = $this->service;
        }

        $insert = $service->insert($this->getConnection());
    }

    protected function doDelete(ORM\Data $data, Service $service = null)
    {
        if ($service === null) {
            $service = $this->service;
        }

        $delete = $service->delete($this->getConnection());

        foreach ($this->config['primaryKeys'] as $primaryKey) {
            $delete->where(sprintf('%s = ?', $primaryKey), $data->get($primaryKey));
        }

        return $delete->execute();
    }

    protected function verify()
    {
        parent::verify();
        $this->verifyConfig('connection');
    }
}