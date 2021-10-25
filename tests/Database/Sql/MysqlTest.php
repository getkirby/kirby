<?php

namespace Kirby\Database\Sql;

use Kirby\Database\Database;
use Kirby\Toolkit\A;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Database\Sql\Mysql
 */
class MysqlTest extends TestCase
{
    protected $database;
    protected $sql;

    public function setUp(): void
    {
        $this->database = new Database([
            'type'     => 'sqlite',
            'database' => ':memory:'
        ]);

        $this->sql = new Mysql($this->database);
    }

    /**
     * @covers ::columns
     */
    public function testColumns()
    {
        $result = $this->sql->columns('test');
        $this->assertSame(':memory:', A::first($result['bindings']));
        $this->assertSame('test', A::last($result['bindings']));
    }

    /**
     * @covers ::tables
     */
    public function testTables()
    {
        $result = $this->sql->tables();
        $this->assertStringStartsWith('SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ', $result['query']);
        $this->assertSame(':memory:', A::first($result['bindings']));
    }
}
