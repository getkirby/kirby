<?php

namespace Kirby\Database\Sql;

use Kirby\Database\Database;
use Kirby\Toolkit\A;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Mysql::class)]
class MysqlTest extends TestCase
{
	public function setUp(): void
	{
		$this->database = new Database([
			'type'     => 'sqlite',
			'database' => ':memory:'
		]);
		$this->database->execute('CREATE TABLE test (id INTEGER)');
		$this->database->execute('CREATE VIEW view_test AS SELECT * FROM test');

		$this->sql = new Mysql($this->database);
	}

	public function testColumns(): void
	{
		$result = $this->sql->columns('test');
		$this->assertSame(':memory:', A::first($result['bindings']));
		$this->assertSame('test', A::last($result['bindings']));
	}

	public function testTables(): void
	{
		$result = $this->sql->tables();
		$this->assertStringStartsWith('SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ', $result['query']);
		$this->assertSame(':memory:', A::first($result['bindings']));
	}

	public function testValidateTable(): void
	{
		$this->assertTrue($this->database->validateTable('test'));
		$this->assertTrue($this->database->validateTable('view_test'));
		$this->assertFalse($this->database->validateTable('not_exist'));
	}
}
