<?php

namespace Kirby\Database;

use Kirby\Toolkit\A;
use PHPUnit\Framework\TestCase;

class SqlTest extends TestCase
{
    public function setUp(): void
    {
        $this->database = new Database([
            'type'     => 'sqlite',
            'database' => ':memory:'
        ]);
    }

    public function testColumns()
    {
        $sql = new Sql($this->database);
        $result = $sql->columns('test');

        $this->assertEquals(':memory:', A::first($result['bindings']));
        $this->assertEquals('test', A::last($result['bindings']));
    }

    public function testTables()
    {
        $sql = new Sql($this->database);
        $result = $sql->tables();

        $this->assertEquals(':memory:', A::first($result['bindings']));
    }
}
