<?php

namespace Kirby\Database;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use ReflectionProperty;

#[CoversClass(Db::class)]
class DbTest extends TestCase
{
	public function setUp(): void
	{
		$this->database = Db::connect([
			'database' => ':memory:',
			'type'     => 'sqlite'
		]);

		// create a dummy user table which we can use for our tests
		Db::execute('
            CREATE TABLE "users" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
            "username" TEXT UNIQUE ON CONFLICT FAIL NOT NULL,
            "fname" TEXT,
            "lname" TEXT,
            "password" TEXT NOT NULL,
            "email" TEXT NOT NULL,
			"active" INTEGER NOT NULL
            );
        ');

		// insert some silly dummy data
		Db::insert('users', [
			'username' => 'john',
			'fname'    => 'John',
			'lname'    => 'Lennon',
			'email'    => 'john@test.com',
			'password' => 'beatles',
			'active'   => true,
		]);

		Db::insert('users', [
			'username' => 'paul',
			'fname'    => 'Paul',
			'lname'    => 'McCartney',
			'email'    => 'paul@test.com',
			'password' => 'beatles',
			'active'   => true,
		]);

		Db::insert('users', [
			'username' => 'george',
			'fname'    => 'George',
			'lname'    => 'Harrison',
			'email'    => 'george@test.com',
			'password' => 'beatles',
			'active'   => false,
		]);
	}

	public function testConnect()
	{
		$db = Db::connect();
		$this->assertInstanceOf(Database::class, $db);

		// cached instance
		$this->assertSame($db, Db::connect());

		// new instance
		$this->assertNotSame($db, Db::connect([
			'type'     => 'sqlite',
			'database' => ':memory:'
		]));

		// new instance with custom options
		$db = Db::connect([
			'type'     => 'sqlite',
			'database' => ':memory:',
			'prefix'   => 'test_'
		]);
		$this->assertSame('test_', $db->prefix());

		// cache of the new instance
		$this->assertSame($db, Db::connect());
	}

	public function testConnection()
	{
		$this->assertInstanceOf(Database::class, Db::connection());
	}

	public function testTable()
	{
		$tableProp = new ReflectionProperty(Query::class, 'table');
		$tableProp->setAccessible(true);

		$query = Db::table('users');
		$this->assertInstanceOf(Query::class, $query);
		$this->assertSame('users', $tableProp->getValue($query));
	}

	public function testQuery()
	{
		$result = Db::query('SELECT * FROM users WHERE username = :username', ['username' => 'paul'], ['fetch' => 'array', 'iterator' => 'array']);
		$this->assertSame('paul', $result[0]['username']);
	}

	public function testExecute()
	{
		$result = Db::query('SELECT * FROM users WHERE username = :username', ['username' => 'paul'], ['fetch' => 'array', 'iterator' => 'array']);
		$this->assertSame('paul', $result[0]['username']);

		$result = Db::execute('DELETE FROM users WHERE username = :username', ['username' => 'paul']);
		$this->assertTrue($result);

		$result = Db::query('SELECT * FROM users WHERE username = :username', ['username' => 'paul'], ['fetch' => 'array', 'iterator' => 'array']);
		$this->assertEmpty($result);
	}

	public function testCallStatic()
	{
		Db::connect([
			'database' => ':memory:',
			'type'     => 'sqlite',
			'prefix'   => 'myprefix_'
		]);

		Db::$queries['test'] = fn ($test) => $test . ' test';
		$this->assertSame('This is a test', Db::test('This is a'));
		unset(Db::$queries['test']);

		$this->assertSame('sqlite', Db::type());
		$this->assertSame('myprefix_', Db::prefix());
	}

	public function testCallStaticInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid static Db method: thisIsInvalid');

		Db::thisIsInvalid();
	}

	#[CoversNothing]
	public function testSelect()
	{
		$result = Db::select('users');
		$this->assertCount(3, $result);

		$result = Db::select('users', 'username', ['username' => 'paul']);
		$this->assertCount(1, $result);
		$this->assertSame('paul', $result->first()->username());

		$result = Db::select('users', 'username', null, 'username ASC', 1, 1);
		$this->assertCount(1, $result);
		$this->assertSame('john', $result->first()->username());

		$result = Db::select('users', 'username', ['active' => false]);
		$this->assertCount(1, $result);
		$this->assertSame('george', $result->first()->username());
	}

	#[CoversNothing]
	public function testFirst()
	{
		$result = Db::first('users');
		$this->assertSame('john', $result->username());

		$result = Db::first('users', '*', ['username' => 'paul']);
		$this->assertSame('paul', $result->username());

		$result = Db::first('users', '*', null, 'username ASC');
		$this->assertSame('george', $result->username());

		$result = Db::row('users');
		$this->assertSame('john', $result->username());

		$result = Db::one('users');
		$this->assertSame('john', $result->username());
	}

	#[CoversNothing]
	public function testColumn()
	{
		$result = Db::column('users', 'username');
		$this->assertSame(['john', 'paul', 'george'], $result->toArray());

		$result = Db::column('users', 'username', ['username' => 'paul']);
		$this->assertSame(['paul'], $result->toArray());

		$result = Db::column('users', 'username', null, 'username ASC');
		$this->assertSame(['george', 'john', 'paul'], $result->toArray());

		$result = Db::column('users', 'username', null, 'username ASC', 1, 1);
		$this->assertSame(['john'], $result->toArray());
	}

	#[CoversNothing]
	public function testInsert()
	{
		$result = Db::insert('users', [
			'username' => 'ringo',
			'fname'    => 'Ringo',
			'lname'    => 'Starr',
			'email'    => 'ringo@test.com',
			'password' => 'beatles',
			'active'   => false,
		]);
		$this->assertSame(4, $result);
		$this->assertSame('ringo@test.com', Db::row('users', '*', ['username' => 'ringo'])->email());
		$this->assertSame('0', Db::row('users', '*', ['username' => 'ringo'])->active());
	}

	#[CoversNothing]
	public function testUpdate()
	{
		$result = Db::update('users', ['email' => 'john@gmail.com'], ['username' => 'john']);
		$this->assertTrue($result);
		$this->assertSame('john@gmail.com', Db::row('users', '*', ['username' => 'john'])->email());
		$this->assertSame('paul@test.com', Db::row('users', '*', ['username' => 'paul'])->email());

		$result = Db::update('users', ['active' => false], ['username' => 'john']);
		$this->assertTrue($result);
		$this->assertSame('0', Db::row('users', '*', ['username' => 'john'])->active());
		$this->assertSame('1', Db::row('users', '*', ['username' => 'paul'])->active());
	}

	#[CoversNothing]
	public function testDelete()
	{
		$result = Db::delete('users', ['username' => 'john']);
		$this->assertTrue($result);
		$this->assertFalse(Db::one('users', '*', ['username' => 'john']));
		$this->assertSame(2, Db::count('users'));
	}

	#[CoversNothing]
	public function testCount()
	{
		$this->assertSame(3, Db::count('users'));
	}

	#[CoversNothing]
	public function testMin()
	{
		$this->assertSame(1.0, Db::min('users', 'id'));
	}

	#[CoversNothing]
	public function testMax()
	{
		$this->assertSame(3.0, Db::max('users', 'id'));
	}

	#[CoversNothing]
	public function testAvg()
	{
		$this->assertSame(2.0, Db::avg('users', 'id'));
	}

	#[CoversNothing]
	public function testSum()
	{
		$this->assertSame(6.0, Db::sum('users', 'id'));
	}
}
