<?php

namespace Kirby\Database;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @coversDefaultClass \Kirby\Database\Db
 */
class DbTest extends TestCase
{
    public function setUp(): void
    {
        Db::connect([
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
            "email" TEXT NOT NULL
            );
        ');

        // insert some silly dummy data
        Db::insert('users', [
            'username' => 'john',
            'fname'    => 'John',
            'lname'    => 'Lennon',
            'email'    => 'john@test.com',
            'password' => 'beatles'
        ]);

        Db::insert('users', [
            'username' => 'paul',
            'fname'    => 'Paul',
            'lname'    => 'McCartney',
            'email'    => 'paul@test.com',
            'password' => 'beatles'
        ]);

        Db::insert('users', [
            'username' => 'george',
            'fname'    => 'George',
            'lname'    => 'Harrison',
            'email'    => 'george@test.com',
            'password' => 'beatles'
        ]);
    }

    /**
     * @covers ::connect
     */
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

    /**
     * @covers ::connection
     */
    public function testConnection()
    {
        $this->assertInstanceOf(Database::class, Db::connection());
    }

    /**
     * @covers ::table
     */
    public function testTable()
    {
        $tableProp = new ReflectionProperty(Query::class, 'table');
        $tableProp->setAccessible(true);

        $query = Db::table('users');
        $this->assertInstanceOf(Query::class, $query);
        $this->assertSame('users', $tableProp->getValue($query));
    }

    /**
     * @covers ::query
     */
    public function testQuery()
    {
        $result = Db::query('SELECT * FROM users WHERE username = :username', ['username' => 'paul'], ['fetch' => 'array', 'iterator' => 'array']);
        $this->assertSame('paul', $result[0]['username']);
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $result = Db::query('SELECT * FROM users WHERE username = :username', ['username' => 'paul'], ['fetch' => 'array', 'iterator' => 'array']);
        $this->assertSame('paul', $result[0]['username']);

        $result = Db::execute('DELETE FROM users WHERE username = :username', ['username' => 'paul']);
        $this->assertTrue($result);

        $result = Db::query('SELECT * FROM users WHERE username = :username', ['username' => 'paul'], ['fetch' => 'array', 'iterator' => 'array']);
        $this->assertEmpty($result);
    }

    /**
     * @covers ::__callStatic
     */
    public function testCallStatic()
    {
        Db::connect([
            'database' => ':memory:',
            'type'     => 'sqlite',
            'prefix'   => 'myprefix_'
        ]);

        Db::$queries['test'] = function ($test) {
            return $test . ' test';
        };
        $this->assertSame('This is a test', Db::test('This is a'));
        unset(Db::$queries['test']);

        $this->assertSame('sqlite', Db::type());
        $this->assertSame('myprefix_', Db::prefix());
    }

    /**
     * @covers ::__callStatic
     */
    public function testCallStaticInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid static Db method: thisIsInvalid');

        Db::thisIsInvalid();
    }

    /**
     * @coversNothing
     */
    public function testSelect()
    {
        $result = Db::select('users');
        $this->assertSame(3, $result->count());

        $result = Db::select('users', 'username', ['username' => 'paul']);
        $this->assertSame(1, $result->count());
        $this->assertSame('paul', $result->first()->username());

        $result = Db::select('users', 'username', null, 'username ASC', 1, 1);
        $this->assertSame(1, $result->count());
        $this->assertSame('john', $result->first()->username());
    }

    /**
     * @coversNothing
     */
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

    /**
     * @coversNothing
     */
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

    /**
     * @coversNothing
     */
    public function testInsert()
    {
        $result = Db::insert('users', [
            'username' => 'ringo',
            'fname'    => 'Ringo',
            'lname'    => 'Starr',
            'email'    => 'ringo@test.com',
            'password' => 'beatles'
        ]);
        $this->assertSame(4, $result);
        $this->assertSame('ringo@test.com', Db::row('users', '*', ['username' => 'ringo'])->email());
    }

    /**
     * @coversNothing
     */
    public function testUpdate()
    {
        $result = Db::update('users', ['email' => 'john@gmail.com'], ['username' => 'john']);
        $this->assertTrue($result);
        $this->assertSame('john@gmail.com', Db::row('users', '*', ['username' => 'john'])->email());
        $this->assertSame('paul@test.com', Db::row('users', '*', ['username' => 'paul'])->email());
    }

    /**
     * @coversNothing
     */
    public function testDelete()
    {
        $result = Db::delete('users', ['username' => 'john']);
        $this->assertTrue($result);
        $this->assertFalse(Db::one('users', '*', ['username' => 'john']));
        $this->assertSame(2, Db::count('users'));
    }

    /**
     * @coversNothing
     */
    public function testCount()
    {
        $this->assertSame(3, Db::count('users'));
    }

    /**
     * @coversNothing
     */
    public function testMin()
    {
        $this->assertSame(1.0, Db::min('users', 'id'));
    }

    /**
     * @coversNothing
     */
    public function testMax()
    {
        $this->assertSame(3.0, Db::max('users', 'id'));
    }

    /**
     * @coversNothing
     */
    public function testAvg()
    {
        $this->assertSame(2.0, Db::avg('users', 'id'));
    }

    /**
     * @coversNothing
     */
    public function testSum()
    {
        $this->assertSame(6.0, Db::sum('users', 'id'));
    }
}
