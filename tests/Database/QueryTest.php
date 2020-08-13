<?php

namespace Kirby\Database;

use PDOException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Database\Database
 */
class QueryTest extends TestCase
{
    protected $database;

    public function setUp(): void
    {
        $this->database = new Database([
            'database' => ':memory:',
            'type'     => 'sqlite'
        ]);

        // create a dummy users and roles table which we can use for our tests
        $this->database->execute('
            CREATE TABLE "users" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
            "role_id" INTEGER NOT NULL,
            "username" TEXT UNIQUE ON CONFLICT FAIL NOT NULL,
            "fname" TEXT,
            "lname" TEXT,
            "password" TEXT NOT NULL,
            "email" TEXT NOT NULL,
            "balance" INTEGER 
            );
        ');

        $this->database->execute('
            CREATE TABLE "roles" (
            "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
            "role" TEXT UNIQUE ON CONFLICT FAIL NOT NULL
            );
        ');

        // insert some silly dummy data for roles
        $this->database->table('roles')->insert([
            'role' => 'admin',
        ]);

        $this->database->table('roles')->insert([
            'role' => 'editor',
        ]);

        $this->database->table('roles')->insert([
            'role' => 'user',
        ]);

        // insert some silly dummy data for users
        $this->database->table('users')->insert([
            'role_id'  => 1,
            'username' => 'john',
            'fname'    => 'John',
            'lname'    => 'Lennon',
            'email'    => 'john@test.com',
            'password' => 'beatles',
            'balance'  => 200
        ]);

        $this->database->table('users')->insert([
            'role_id'  => 2,
            'username' => 'paul',
            'fname'    => 'Paul',
            'lname'    => 'McCartney',
            'email'    => 'paul@test.com',
            'password' => 'beatles',
            'balance'  => 150
        ]);

        $this->database->table('users')->insert([
            'role_id'  => 3,
            'username' => 'george',
            'fname'    => 'George',
            'lname'    => 'Harrison',
            'email'    => 'george@test.com',
            'password' => 'beatles',
            'balance'  => 100
        ]);

        $this->database->table('users')->insert([
            'role_id'  => 3,
            'username' => 'mark',
            'fname'    => 'Mark',
            'lname'    => 'Otto',
            'email'    => 'mark@test.com',
            'password' => 'beatles',
            'balance'  => 50
        ]);
    }

    public function testJoin()
    {
        $user = $this->database
            ->table('users')
            ->join('roles', 'roles.id = users.role_id')
            ->where([
                'username' => 'john'
            ])
            ->first();

        $this->assertSame('admin', $user->role());
    }

    public function testOrder()
    {
        $user = $this->database
            ->table('users')
            ->order('username desc')
            ->first();

        $this->assertSame('paul', $user->username());
    }

    public function testGroup()
    {
        $user = $this->database
            ->table('users')
            ->group('role_id')
            ->where([
                'role_id' => 3
            ])
            ->first();

        $this->assertSame('george', $user->username());
    }

    public function testAvg()
    {
        $balance = $this->database
            ->table('users')
            ->group('role_id')
            ->where([
                'role_id' => 3
            ])
            ->avg('balance');

        $this->assertSame((float)75, $balance);
    }

    public function testCount()
    {
        $count = $this->database
            ->table('users')
            ->where([
                'role_id' => 3
            ])
            ->count();

        $this->assertSame(2, $count);
    }
}
