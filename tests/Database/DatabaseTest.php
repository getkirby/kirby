<?php

namespace Kirby\Database;

use PDOException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Database\Database
 */
class DatabaseTest extends TestCase
{
    protected $database;

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
        $result = $this->database->table('users')->select('nonexisting')->all();
        $this->assertInstanceOf(PDOException::class, $this->database->lastError());
    }
}
