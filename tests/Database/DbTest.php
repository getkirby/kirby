<?php

namespace Kirby\Database;

use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    public static $database = null;

    public function setUp(): void
    {
        self::$database = ':memory:';

        Db::connect([
            'database' => self::$database,
            'type'     => 'sqlite'
        ]);

        // create a dummy user table, which we can use for our tests
        Db::query('

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

    public static function tearDownAfterClass()
    {
        // kill the database
        F::remove(self::$database);
    }

    public function testConnect()
    {
        $this->assertInstanceOf('Database', Db::connect());
    }

    public function testConnection()
    {
        $this->assertInstanceOf('Database', Db::connection());
    }

    public function testType()
    {
        Db::connect([
            'database' => self::$database,
            'type'     => 'sqlite'
        ]);

        $this->assertEquals('sqlite', Db::type());
    }

    public function testPrefix()
    {
        Db::connect([
            'database' => self::$database,
            'type'     => 'sqlite',
            'prefix'   => 'myprefix_'
        ]);

        $this->assertEquals('myprefix_', Db::prefix());

        Db::connect([
            'database' => self::$database,
            'type'     => 'sqlite'
        ]);
    }

    public function testLastId()
    {
        $id = Db::insert('users', [
            'username' => 'ringo',
            'fname'    => 'Ringo',
            'lname'    => 'Starr',
            'email'    => 'ringo@test.com',
            'password' => 'beatles'
        ]);

        $this->assertEquals(4, $id);
        $this->assertEquals($id, Db::lastId());
    }

    public function testLastResult()
    {
        $result = Db::select('users', '*');
        $this->assertEquals($result, Db::lastResult());
    }

    public function testLastError()
    {
        $result = Db::select('users', 'nonexisting');
        $this->assertInstanceOf('PDOException', Db::lastError());
    }

    public function testQuery()
    {
        $result = Db::query('select * from users where username = :username', ['username' => 'paul'], ['fetch' => 'array', 'iterator' => 'array']);

        $this->assertEquals('paul', $result[0]['username']);
    }

    public function testTable()
    {
        $this->assertInstanceOf('Kirby\Database\Query', Db::table('users'));
    }

    public function testSelect()
    {
        $result = Db::select('users');

        $this->assertEquals(3, $result->count());

        $result = Db::select('users', '*', ['username' => 'paul']);

        $this->assertEquals(1, $result->count());
    }

    public function testFirst()
    {
        $result = Db::first('users');
        $this->assertEquals('john', $result->username());
    }

    public function testColumn()
    {
        $result = Db::column('users', 'username');
        $this->assertEquals(['john', 'paul', 'george'], $result->toArray());
    }

    public function testUpdate()
    {
        Db::update('users', ['email' => 'john@gmail.com'], ['username' => 'john']);
        $this->assertEquals('john@gmail.com', Db::row('users', '*', ['username' => 'john'])->email());
    }

    public function testDelete()
    {
        Db::delete('users', array('username' => 'ringo'));
        $this->assertFalse(Db::one('users', '*', array('username' => 'ringo')));
    }

    public function testCount()
    {
        $this->assertEquals(3, Db::count('users'));
    }

    public function testMin()
    {
        $this->assertEquals(1, Db::min('users', 'id'));
    }

    public function testMax()
    {
        $this->assertEquals(3, Db::max('users', 'id'));
    }

    public function testAvg()
    {
        $this->assertEquals(2.0, Db::avg('users', 'id'));
    }

    public function testSum()
    {
        $this->assertEquals(6, Db::sum('users', 'id'));
    }

    public function testAffected()
    {
        Db::delete('users');
        $this->assertEquals(3, Db::affected());
    }
}
