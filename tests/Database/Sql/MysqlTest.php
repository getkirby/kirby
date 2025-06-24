<?php

namespace Kirby\Database\Sql;

use Kirby\Database\Database;
use Kirby\Toolkit\A;

/**
 * @coversDefaultClass \Kirby\Database\Sql\Mysql
 */
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

	/**
	 * @covers ::tables
	 */
	public function testValidateTable()
	{
		$this->assertTrue($this->database->validateTable('test'));
		$this->assertTrue($this->database->validateTable('view_test'));
		$this->assertFalse($this->database->validateTable('not_exist'));
	}
}
