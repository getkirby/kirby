<?php

namespace Kirby\Database;

use Kirby\Database\Sql\Sqlite;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Database\Sql
 */
class SqlTest extends TestCase
{
    protected $database;
    protected $sql;

    public function setUp(): void
    {
        $this->database = new Database([
            'type'     => 'sqlite',
            'database' => ':memory:'
        ]);
        $this->database->execute('CREATE TABLE test (id INTEGER)');
        $this->database->execute('CREATE TABLE another (id INTEGER)');

        $this->sql = new MockSql($this->database);
    }

    /**
     * @covers ::bindingName
     */
    public function testBindingName()
    {
        $result = $this->sql->bindingName('test_binding1');
        $this->assertMatchesRegularExpression('/^:test_binding1_[a-zA-Z0-9]{8}$/', $result);

        $result = $this->sql->bindingName('"; inject something');
        $this->assertMatchesRegularExpression('/^:invalid_[a-zA-Z0-9]{8}$/', $result);

        $result = $this->sql->bindingName('');
        $this->assertMatchesRegularExpression('/^:invalid_[a-zA-Z0-9]{8}$/', $result);
    }

    /**
     * @covers ::columnDefault
     */
    public function testColumnDefault()
    {
        $this->assertSame([
            'query'    => null,
            'bindings' => []
        ], $this->sql->columnDefault('test_col', []));

        $result = $this->sql->columnDefault('test_col', ['default' => 'amazing default']);
        $this->assertMatchesRegularExpression('/^DEFAULT :test_col_default_[a-zA-Z0-9]{8}$/', $result['query']);
        $this->assertSame('amazing default', A::first($result['bindings']));
    }

    /**
     * @covers ::columnName
     */
    public function testColumnName()
    {
        // test with the SQLite class because of its more
        // complex `combineIdentifier()` implementation
        $sql = new Sqlite($this->database);

        $this->assertSame('"id"', $sql->columnName('test', 'id'));
        $this->assertSame('"test"."id"', $sql->columnName('test', 'id', true));
        $this->assertSame('"id"', $sql->columnName('test', 'another.id'));
        $this->assertSame('"another"."id"', $sql->columnName('test', 'another.id', true));
        $this->assertNull($sql->columnName('test', 'invalid.id', true));
    }

    /**
     * @covers ::combineIdentifier
     */
    public function testCombineIdentifier()
    {
        $this->assertSame('`test`.`id`', $this->sql->combineIdentifier('test', 'id'));
        $this->assertSame('`test`.*', $this->sql->combineIdentifier('test', '*'));
        $this->assertSame('`test`.`some``column`', $this->sql->combineIdentifier('test', 'some`column'));
        $this->assertSame('`test`.`id`', $this->sql->combineIdentifier('test', 'id', true));
    }

    /**
     * @covers ::createColumn
     */
    public function testCreateColumn()
    {
        // basic example
        $column = $this->sql->createColumn('test', [
            'type' => 'varchar'
        ]);
        $this->assertSame('`test` varchar(255) NULL', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertNull($column['key']);
        $this->assertFalse($column['unique']);

        // explicit NULL value
        $column = $this->sql->createColumn('test', [
            'type' => 'varchar',
            'null' => true
        ]);
        $this->assertSame('`test` varchar(255) NULL', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertNull($column['key']);
        $this->assertFalse($column['unique']);

        // NULL = false
        $column = $this->sql->createColumn('test', [
            'type' => 'varchar',
            'null' => false
        ]);
        $this->assertSame('`test` varchar(255) NOT NULL', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertNull($column['key']);
        $this->assertFalse($column['unique']);

        // key
        $column = $this->sql->createColumn('test', [
            'type' => 'varchar',
            'key'  => 'PRIMARY'
        ]);
        $this->assertSame('`test` varchar(255) NULL', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertSame('primary', $column['key']);
        $this->assertFalse($column['unique']);

        // automatic key
        $column = $this->sql->createColumn('test', [
            'type' => 'varchar',
            'key'  => true
        ]);
        $this->assertSame('`test` varchar(255) NULL', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertSame('test_index', $column['key']);
        $this->assertFalse($column['unique']);

        // explicitly not unique
        $column = $this->sql->createColumn('test', [
            'type'   => 'varchar',
            'unique' => false
        ]);
        $this->assertSame('`test` varchar(255) NULL', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertNull($column['key']);
        $this->assertFalse($column['unique']);

        // unique without key
        $column = $this->sql->createColumn('test', [
            'type'   => 'varchar',
            'unique' => true
        ]);
        $this->assertSame('`test` varchar(255) NULL  UNIQUE', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertNull($column['key']);
        $this->assertFALSE($column['unique']);

        // unique with key
        $column = $this->sql->createColumn('test', [
            'type'   => 'varchar',
            'key'    => 'a_test',
            'unique' => true
        ]);
        $this->assertSame('`test` varchar(255) NULL', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertSame('a_test', $column['key']);
        $this->assertTrue($column['unique']);

        // unique with automatic key
        $column = $this->sql->createColumn('test', [
            'type'   => 'varchar',
            'key'    => true,
            'unique' => true
        ]);
        $this->assertSame('`test` varchar(255) NULL', $column['query']);
        $this->assertSame([], $column['bindings']);
        $this->assertSame('test_index', $column['key']);
        $this->assertTrue($column['unique']);

        // default
        $column = $this->sql->createColumn('test', [
            'type'    => 'varchar',
            'default' => 'amazing default'
        ]);
        $this->assertStringStartsWith('`test` varchar(255) NULL DEFAULT :', $column['query']);
        $this->assertSame('amazing default', A::first($column['bindings']));
        $this->assertNull($column['key']);
        $this->assertFalse($column['unique']);

        // fail with no type
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->sql->createColumn('test', [
            'default' => 'amazing default'
        ]);

        // fail with invalid type
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->sql->createColumn('test', [
            'type'    => 'nonexisting',
            'default' => 'amazing default'
        ]);
    }

    /**
     * @covers ::createColumn
     */
    public function testCreateColumnNoType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('No column type given for column test');

        $this->sql->createColumn('test', []);
    }

    /**
     * @covers ::createColumn
     */
    public function testCreateColumnInvalidType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Unsupported column type: invalid');

        $this->sql->createColumn('test', ['type' => 'invalid']);
    }

    public function testCreateTableInner()
    {
        // basic example
        $inner = $this->sql->createTableInner([
            'test'    => ['type' => 'varchar'],
            'another' => ['type' => 'varchar', 'null' => false]
        ]);
        $this->assertSame('`test` varchar(255) NULL,' . PHP_EOL . '`another` varchar(255) NOT NULL', $inner['query']);
        $this->assertSame([], $inner['bindings']);
        $this->assertSame([], $inner['keys']);
        $this->assertSame([], $inner['unique']);

        // with default values
        $inner = $this->sql->createTableInner([
            'test'    => ['type' => 'varchar', 'default' => 'test default'],
            'another' => ['type' => 'varchar', 'default' => 'another default']
        ]);
        $this->assertMatchesRegularExpression('/^`test` varchar\(255\) NULL DEFAULT :(.*?),' . PHP_EOL . '`another` varchar\(255\) NULL DEFAULT :(.*)$/m', $inner['query']);
        $this->assertSame(2, count($inner['bindings']));
        $this->assertSame('test default', A::first($inner['bindings']));
        $this->assertSame('another default', A::last($inner['bindings']));
        $this->assertSame([], $inner['keys']);
        $this->assertSame([], $inner['unique']);

        // with keys
        $inner = $this->sql->createTableInner([
            'test'    => ['type' => 'varchar', 'key' => 'test'],
            'another' => ['type' => 'varchar', 'key' => 'another']
        ]);
        $this->assertSame('`test` varchar(255) NULL,' . PHP_EOL . '`another` varchar(255) NULL', $inner['query']);
        $this->assertSame([], $inner['bindings']);
        $this->assertSame(['test' => ['test'], 'another' => ['another']], $inner['keys']);
        $this->assertSame([], $inner['unique']);

        // with a multi-column key
        $inner = $this->sql->createTableInner([
            'test'    => ['type' => 'varchar', 'key' => 'test'],
            'another' => ['type' => 'varchar', 'key' => 'test']
        ]);
        $this->assertSame('`test` varchar(255) NULL,' . PHP_EOL . '`another` varchar(255) NULL', $inner['query']);
        $this->assertSame([], $inner['bindings']);
        $this->assertSame(['test' => ['test', 'another']], $inner['keys']);
        $this->assertSame([], $inner['unique']);

        // with a multi-column unique key
        $inner = $this->sql->createTableInner([
            'test'    => ['type' => 'varchar', 'key' => 'test', 'unique' => true],
            'another' => ['type' => 'varchar', 'key' => 'test']
        ]);
        $this->assertSame('`test` varchar(255) NULL,' . PHP_EOL . '`another` varchar(255) NULL', $inner['query']);
        $this->assertSame([], $inner['bindings']);
        $this->assertSame(['test' => ['test', 'another']], $inner['keys']);
        $this->assertSame(['test' => true], $inner['unique']);
    }

    public function testCreateTable()
    {
        // basic example
        $table = $this->sql->createTable('table', [
            'test'    => ['type' => 'varchar'],
            'another' => ['type' => 'varchar', 'null' => false]
        ]);
        $this->assertSame(
            'CREATE TABLE `table` (' . PHP_EOL .
            '`test` varchar(255) NULL,' . PHP_EOL .
            '`another` varchar(255) NOT NULL' . PHP_EOL .
            ')',
            $table['query']
        );
        $this->assertSame([], $table['bindings']);

        // with default values
        $table = $this->sql->createTable('table', [
            'test'    => ['type' => 'varchar', 'default' => 'test default'],
            'another' => ['type' => 'varchar', 'default' => 'another default']
        ]);
        $this->assertMatchesRegularExpression(
            '/^CREATE TABLE `table` \(' . PHP_EOL .
            '`test` varchar\(255\) NULL DEFAULT :(.*?),' . PHP_EOL .
            '`another` varchar\(255\) NULL DEFAULT :(.*)' . PHP_EOL .
            '\)$/m',
            $table['query']
        );
        $this->assertSame(2, count($table['bindings']));
        $this->assertSame('test default', A::first($table['bindings']));
        $this->assertSame('another default', A::last($table['bindings']));

        // with key
        $table = $this->sql->createTable('table', [
            'test'    => ['type' => 'varchar', 'key' => 'test'],
            'another' => ['type' => 'varchar', 'key' => 'test']
        ]);
        $this->assertSame(
            'CREATE TABLE `table` (' . PHP_EOL .
            '`test` varchar(255) NULL,' . PHP_EOL .
            '`another` varchar(255) NULL,' . PHP_EOL .
            'INDEX `test` (`test`, `another`)' . PHP_EOL .
            ')',
            $table['query']
        );
        $this->assertSame([], $table['bindings']);

        // with primary key
        $table = $this->sql->createTable('table', [
            'test'    => ['type' => 'varchar', 'key' => 'primary'],
            'another' => ['type' => 'varchar', 'key' => 'test']
        ]);
        $this->assertSame(
            'CREATE TABLE `table` (' . PHP_EOL .
            '`test` varchar(255) NULL,' . PHP_EOL .
            '`another` varchar(255) NULL,' . PHP_EOL .
            'PRIMARY KEY (`test`),' . PHP_EOL .
            'INDEX `test` (`another`)' . PHP_EOL .
            ')',
            $table['query']
        );
        $this->assertSame([], $table['bindings']);

        // with unique key
        $table = $this->sql->createTable('table', [
            'test'    => ['type' => 'varchar', 'key' => 'test', 'unique' => true],
            'another' => ['type' => 'varchar', 'key' => 'test', 'unique' => true]
        ]);
        $this->assertSame(
            'CREATE TABLE `table` (' . PHP_EOL .
            '`test` varchar(255) NULL,' . PHP_EOL .
            '`another` varchar(255) NULL,' . PHP_EOL .
            'UNIQUE INDEX `test` (`test`, `another`)' . PHP_EOL .
            ')',
            $table['query']
        );
        $this->assertSame([], $table['bindings']);
    }

    /**
     * @covers ::quoteIdentifier
     */
    public function testQuoteIdentifier()
    {
        $this->assertSame('*', $this->sql->quoteIdentifier('*'));
        $this->assertSame('`test`', $this->sql->quoteIdentifier('test'));
        $this->assertSame('`another``test`', $this->sql->quoteIdentifier('another`test'));
        $this->assertSame('`another"test`', $this->sql->quoteIdentifier('another"test'));
        $this->assertSame("`another'test`", $this->sql->quoteIdentifier("another'test"));
    }

    /**
     * @covers ::splitIdentifier
     */
    public function testSplitIdentifier()
    {
        $result = $this->sql->splitIdentifier('table', 'table.column');
        $this->assertSame(['table', 'column'], $result);

        $result = $this->sql->splitIdentifier('table', 'table2.column');
        $this->assertSame(['table2', 'column'], $result);

        $result = $this->sql->splitIdentifier('table', 'column');
        $this->assertSame(['table', 'column'], $result);

        $result = $this->sql->splitIdentifier('table', '"table.column"');
        $this->assertSame(['table', 'table.column'], $result);

        $result = $this->sql->splitIdentifier('table', '`table.column`');
        $this->assertSame(['table', 'table.column'], $result);

        $result = $this->sql->splitIdentifier('table', 'table."table.column"');
        $this->assertSame(['table', 'table.column'], $result);

        $result = $this->sql->splitIdentifier('table', 'table.`table.column`');
        $this->assertSame(['table', 'table.column'], $result);

        $result = $this->sql->splitIdentifier('table', '`table.name`.`column.name`');
        $this->assertSame(['table.name', 'column.name'], $result);

        $result = $this->sql->splitIdentifier('table', '"table.name"."column.name"');
        $this->assertSame(['table.name', 'column.name'], $result);

        $result = $this->sql->splitIdentifier('table', '`table.name`."column.name"');
        $this->assertSame(['table.name', 'column.name'], $result);

        $result = $this->sql->splitIdentifier('table', '"table.name".`column.name`');
        $this->assertSame(['table.name', 'column.name'], $result);
    }

    /**
     * @covers ::splitIdentifier
     */
    public function testSplitIdentifierInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid identifier table.column.invalid');

        $this->sql->splitIdentifier('table', 'table.column.invalid');
    }

    public function testFrom()
    {
        $this->database->createTable('users', [
            'test' => ['type' => 'varchar']
        ]);

        $this->assertSame([
            'query'    => 'FROM `users`',
            'bindings' => []
        ], $this->sql->from('users'));
    }

    public function testGroup()
    {
        $this->database->createTable('users', [
            'test' => ['type' => 'varchar']
        ]);

        $this->assertSame([
            'query'    => 'GROUP BY test',
            'bindings' => []
        ], $this->sql->group('test'));
    }

    public function testHaving()
    {
        $this->database->createTable('users', [
            'test' => ['type' => 'varchar']
        ]);

        $this->assertSame([
            'query'    => 'HAVING test < :value',
            'bindings' => []
        ], $this->sql->having('test < :value'));
    }
}
