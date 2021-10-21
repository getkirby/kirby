<?php

namespace Kirby\Database\Sql;

use Kirby\Database\Database;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Database\Sql\Sqlite
 */
class SqliteTest extends TestCase
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

        $this->sql = new Sqlite($this->database);
    }

    /**
     * @covers ::columns
     */
    public function testColumns()
    {
        $result = $this->sql->columns('test');
        $this->assertSame('PRAGMA table_info("test")', $result['query']);
    }

    /**
     * @covers ::columns
     */
    public function testColumnsInvalidTable()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table does-not-exist');

        $this->sql->columns('does-not-exist');
    }

    /**
     * @covers ::combineIdentifier
     */
    public function testCombineIdentifier()
    {
        $this->assertSame('"test"."id"', $this->sql->combineIdentifier('test', 'id'));
        $this->assertSame('"test".*', $this->sql->combineIdentifier('test', '*'));
        $this->assertSame('"test"."some""column"', $this->sql->combineIdentifier('test', 'some"column'));
        $this->assertSame('"test"."some`column"', $this->sql->combineIdentifier('test', 'some`column'));
        $this->assertSame('"test"."some\'column"', $this->sql->combineIdentifier('test', "some'column"));
        $this->assertSame('"id"', $this->sql->combineIdentifier('test', 'id', true));
    }

    /**
     * @covers ::createTable
     */
    public function testCreateTable()
    {
        // basic example
        $table = $this->sql->createTable('table', [
            'test'    => ['type' => 'varchar'],
            'another' => ['type' => 'varchar', 'null' => false]
        ]);
        $this->assertSame(
            'CREATE TABLE "table" (' . PHP_EOL .
            '"test" TEXT NULL,' . PHP_EOL .
            '"another" TEXT NOT NULL' . PHP_EOL .
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
            '/^CREATE TABLE "table" \(' . PHP_EOL .
            '"test" TEXT NULL DEFAULT :(.*?),' . PHP_EOL .
            '"another" TEXT NULL DEFAULT :(.*)' . PHP_EOL .
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
            'CREATE TABLE "table" (' . PHP_EOL .
            '"test" TEXT NULL,' . PHP_EOL .
            '"another" TEXT NULL' . PHP_EOL .
            ');' . PHP_EOL .
            'CREATE INDEX "table_index_test" ON "table" ("test", "another")',
            $table['query']
        );
        $this->assertSame([], $table['bindings']);

        // with primary key
        $table = $this->sql->createTable('table', [
            'test'    => ['type' => 'varchar', 'key' => 'primary'],
            'another' => ['type' => 'varchar', 'key' => 'test']
        ]);
        $this->assertSame(
            'CREATE TABLE "table" (' . PHP_EOL .
            '"test" TEXT NULL,' . PHP_EOL .
            '"another" TEXT NULL,' . PHP_EOL .
            'PRIMARY KEY ("test")' . PHP_EOL .
            ');' . PHP_EOL .
            'CREATE INDEX "table_index_test" ON "table" ("another")',
            $table['query']
        );
        $this->assertSame([], $table['bindings']);

        // with unique key
        $table = $this->sql->createTable('table', [
            'test'    => ['type' => 'varchar', 'key' => 'test', 'unique' => true],
            'another' => ['type' => 'varchar', 'key' => 'test', 'unique' => true]
        ]);
        $this->assertSame(
            'CREATE TABLE "table" (' . PHP_EOL .
            '"test" TEXT NULL,' . PHP_EOL .
            '"another" TEXT NULL' . PHP_EOL .
            ');' . PHP_EOL .
            'CREATE UNIQUE INDEX "table_index_test" ON "table" ("test", "another")',
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
        $this->assertSame('"test"', $this->sql->quoteIdentifier('test'));
        $this->assertSame('"another""test"', $this->sql->quoteIdentifier('another"test'));
        $this->assertSame('"another`test"', $this->sql->quoteIdentifier('another`test'));
        $this->assertSame('"another\'test"', $this->sql->quoteIdentifier("another'test"));
    }

    /**
     * @covers ::tables
     */
    public function testTables()
    {
        $result = $this->sql->tables();
        $this->assertSame('SELECT name FROM sqlite_master WHERE type = "table"', $result['query']);
        $this->assertSame([], $result['bindings']);
    }
}
