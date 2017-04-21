<?php

namespace Tests\Service\Sql;

use Etu\Service\Sql\Mysql;

/**
 * Class MysqlTest
 * @author mucean
 */
class MysqlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Mysql
     */
    protected $db;

    /**
     * @before
     */
    public function getConfig()
    {
        $config = require __DIR__ . '/config.php';
        if (isset($config['mysql']) === false) {
            throw new \RuntimeException('mysql config is not fount!');
        }

        $mysqlConfig = $config['mysql'];
 
        $dns = sprintf('mysql:host=%s;dbname=%s', $mysqlConfig['host'], $mysqlConfig['dbName']);

        $this->db = new Mysql([
            'dsn' => $dns,
            'user' => $mysqlConfig['user'],
            'password' => $mysqlConfig['password']
        ]);
    }

    public function close()
    {
        $this->db->close();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Mysql::class, $this->db);
    }

    public function testConnect()
    {
        $this->assertInstanceOf(\PDO::class, $this->db->connect());
    }

    public function testGetPDO()
    {
        $this->assertInstanceOf(\PDO::class, $this->db->getPDO());
    }
}
