<?php

namespace Kirby\Database;

use Kirby\Exception\InvalidArgumentException;
use PDO;
use PDOException;

/**
 * @coversDefaultClass \Kirby\Database\Database
 */
class DatabaseTest extends TestCase
{
	public function setUp(): void
	{
		$this->database = new Database([
			'database' => ':memory:',
			'type'     => 'sqlite'
		]);

		// create a dummy user table which we can use for our tests
		$this->database->execute('
            CREATE TABLE "users" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
            "username" TEXT UNIQUE ON CONFLICT FAIL NOT NULL,
            "fname" TEXT,
            "lname" TEXT,
            "password" TEXT NOT NULL,
            "email" TEXT NOT NULL
            );
        ');

		// insert some silly dummy data
		$this->database->table('users')->insert([
			'username' => 'john',
			'fname'    => 'John',
			'lname'    => 'Lennon',
			'email'    => 'john@test.com',
			'password' => 'beatles'
		]);

		$this->database->table('users')->insert([
			'username' => 'paul',
			'fname'    => 'Paul',
			'lname'    => 'McCartney',
			'email'    => 'paul@test.com',
			'password' => 'beatles'
		]);

		$this->database->table('users')->insert([
			'username' => 'george',
			'fname'    => 'George',
			'lname'    => 'Harrison',
			'email'    => 'george@test.com',
			'password' => 'beatles'
		]);
	}

	public function tearDown(): void
	{
		Database::$connections = [];
	}

	public function testInstance()
	{
		$this->assertSame($this->database, Database::instance());
	}

	public function testInstances()
	{
		// create another instance
		$secondInstance = new Database([
			'database' => ':memory:',
			'type'     => 'sqlite'
		]);

		$this->assertSame([
			$this->database,
			$secondInstance
		], array_values(Database::instances()));
	}

	/**
	 * @covers ::affected
	 */
	public function testAffected()
	{
		$this->database->table('users')->delete();
		$this->assertSame(3, $this->database->affected());
	}

	/**
	 * @covers ::lastId
	 */
	public function testLastId()
	{
		$id = $this->database->table('users')->insert([
			'username' => 'ringo',
			'fname'    => 'Ringo',
			'lname'    => 'Starr',
			'email'    => 'ringo@test.com',
			'password' => 'beatles'
		]);

		$this->assertSame(4, $id);
		$this->assertSame($id, $this->database->lastId());
	}

	/**
	 * @covers ::lastResult
	 */
	public function testLastResult()
	{
		$result = $this->database->table('users')->select('*')->all();
		$this->assertSame($result, $this->database->lastResult());
	}

	/**
	 * @covers ::lastError
	 */
	public function testLastError()
	{
		$this->assertNull($this->database->lastError());
		$this->database->table('users')->select('nonexisting')->all();
		$this->assertInstanceOf(PDOException::class, $this->database->lastError());
	}

	public function testConnect()
	{
		$db = new Database([
			'database' => ':memory:',
			'type'     => 'sqlite'
		]);

		$this->assertInstanceOf(Database::class, $db);
		$this->expectException(InvalidArgumentException::class);

		new Database([
			'database' => ':memory:',
			'type'     => 'nonexisting'
		]);
	}

	public function testConnectConnection()
	{
		$this->assertInstanceOf(PDO::class, $this->database->connection());
	}

	public function testFail()
	{
		$this->expectException(PDOException::class);

		$this->database
			->fail()
			->table('users')->select('nonexisting')->all();
	}

	public function testDropTable()
	{
		$this->assertTrue($this->database->dropTable('users'));

		$this->expectException(InvalidArgumentException::class);
		$this->database->fail()->dropTable('nonexisting');
	}

	public function testValidateTable()
	{
		$this->database->validateTable('users');
		$this->assertTrue($this->database->dropTable('users'));
	}

	public function testMagicCall()
	{
		$this->assertCount(3, $this->database->users()->all());
	}

	public function testType()
	{
		$this->assertSame('sqlite', $this->database->type());
	}

	public function testEscape()
	{
		$this->assertSame("sql''inject", $this->database->escape("sql'inject"));
	}

	public function testLastQuery()
	{
		$this->database->users()->all();
		$this->assertSame('SELECT * FROM "users"', $this->database->lastQuery());
	}

	public function testName()
	{
		$this->assertSame(':memory:', $this->database->name());
	}

	public function testCreateTable()
	{
		$this->assertFalse($this->database->createTable('test'));
		$this->assertTrue($this->database->createTable('test', [
			'id' => [
				'type' => 'int',
				'primary' => true
			]
		]));
	}

	public function testMysqlConnector()
	{
		$dsn = Database::$types['mysql']['dsn'];
		$this->assertInstanceOf('Closure', $dsn);

		// valid
		$connectionString = $dsn([
			'host' => 'localhost',
			'database' => 'kirby',
			'charset' => 'iso-8859-1',
			'socket' => '/tmp/mysql.sock',
			'port' => 3306,
		]);

		$expected = 'mysql:host=localhost;port=3306;unix_socket=/tmp/mysql.sock;dbname=kirby;charset=iso-8859-1';
		$this->assertSame($expected, $connectionString);
	}

	public function testMysqlConnectorNoSocketHost()
	{
		$dsn = Database::$types['mysql']['dsn'];

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The mysql connection requires either a "host" or a "socket" parameter');

		$dsn([]);
	}

	public function testMysqlConnectorNoDatabase()
	{
		$dsn = Database::$types['mysql']['dsn'];

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The mysql connection requires a "database" parameter');

		$dsn(['host' => 'localhost']);
	}

	public function testSqliteConnector()
	{
		$dsn = Database::$types['sqlite']['dsn'];
		$this->assertInstanceOf('Closure', $dsn);

		// valid
		$connectionString = $dsn([
			'database' => 'kirby'
		]);

		$this->assertSame('sqlite:kirby', $connectionString);

		// no database
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The sqlite connection requires a "database" parameter');

		$dsn([]);
	}
}
