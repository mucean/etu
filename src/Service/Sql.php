<?php

namespace Etu\Service;

use Etu\Service;
use InvalidArgumentException;
use Exception;
use PDOStatement;
use PDO;

/**
 * Class Sql
 * @author mucean
 */
class Sql extends Service
{
    protected $handler;

    protected $errorHandler;

    protected $identifier = '`';

    public function __construct(array $config)
    {
        if (!array_key_exists('dsn', $config)) {
            throw new InvalidArgumentException('relational database config need `dsn` key');
        }

        parent::__construct($config);
    }

    public function __call($method, $arguments)
    {
        $handler = $this->connect();
        $arguments === []
            ? $handler->$method()
            : call_user_method_array($method, $handler, $arguments);
    }

    /**
     * update database
     *
     * @return int
     */
    public function update($table, array $column, array $where)
    {
    }

    /**
     * prepare update sql
     *
     * @return PDOStatement
     */
    public function prepareUpdate($table, array $column, array $where)
    {
        $set = '';
        foreach ($column as $key => $value) {
        }
    }

    /**
     * execute a sql
     *
     * @return PDOStatement
     */
    public function execute($sql, array $parameters)
    {
        $statement = $sql;
        if (!$statement instanceof PDOStatement) {
            $statement = $this->connect()->prepare($sql);
        }

        if (!$statement->execute($parameters)) {
            throw new Exception('execute sql failed');
        }

        return $statement;
    }

    /**
     * quote sql identifier
     *
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        if (is_array($identifier)) {
            return array_map([$this, 'quoteIdentifier'], $identifier);
        }

        $identifier = str_replace(['\'', '"', ';'], $this->identifier, $identifier);

        return $identifier;
    }

    public function connect()
    {
        if ($this->isConnected()) {
            return $this->handler;
        }

        $dsn = $this->getConfig('dsn');
        $user = $this->getConfig('user');
        $password = $this->getConfig('password');
        $options = $this->getConfig('options', []);
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

        try {
            $db = new PDO($dsn, $user, $password, $options);
        } catch (Exception $e) {
            $this->handleError($e);
            throw new Exception('connect to database failed');
        }

        return $this->handler = $db;
    }

    public function isConnected()
    {
        return $this->handler instanceof PDO;
    }

    public function setErrorHandler(callable $handler)
    {
        $this->errorHandler = $handler;
    }

    protected function handleError(Exception $e)
    {
        if (is_callable($this->errorHandler)) {
            $this->errorHandler($e);
        }
    }
}
