<?php

namespace Tests\Service;

use Etu\Service\Sql;

/**
 * Class SqlTest
 * @author mucean <mocean.liu@gmail.com>
 */
class SqlTest extends \PHPUnit\Framework\TestCase
{
    protected $db;

    public function __construct()
    {
        $config = require __DIR__ . '/Sql/config.php';
        if (isset($config['mysql']) === false) {
            throw new \RuntimeException('mysql config is not fount!');
        }

        $mysqlConfig = $config['mysql'];

        $dns = sprintf('mysql:host=%s;dbname=%s', $mysqlConfig['host'], $mysqlConfig['dbName']);
        $this->db = new Sql([
            'dsn' => $dns,
            'user' => $mysqlConfig['user'],
            'password' => $mysqlConfig['password']
        ]);
        parent::__construct();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Sql::class, $this->db);
    }

    public function testConnect()
    {
        $this->assertInstanceOf(\PDO::class, $this->db->connect());
    }
}
