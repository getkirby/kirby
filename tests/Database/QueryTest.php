<?php

namespace Kirby\Database;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Database\Database
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

    public function testInnerJoin()
    {
        $user = $this->database
            ->table('users')
            ->innerJoin('roles', 'roles.id = users.role_id')
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
        $sum = $this->database
            ->table('users')
            ->group('role_id')
            ->where([
                'role_id' => 3
            ])
            ->sum('balance');

        $this->assertSame((float)150, $sum);
    }

    public function testSum()
    {
        $sum = $this->database
            ->table('users')
            ->sum('balance');

        $this->assertSame((float)500, $sum);
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

    public function testQuery()
    {
        $result = $this->database
            ->query('SELECT * FROM users WHERE role_id = :role', ['role' => 3]);

        $this->assertCount(2, $result->data());
    }

    public function testUpdate()
    {
        // update
        $update = $this->database
            ->table('users')
            ->update(['balance' => 250], ['id' => 1]);

        $this->assertTrue($update);

        // check updated user value
        $user = $this->database
            ->table('users')
            ->where(['id' => 1])
            ->first();

        $this->assertSame('250', $user->balance());
    }

    public function testDelete()
    {
        $delete = $this->database
            ->table('users')
            ->delete(['id' => 4]);

        $this->assertTrue($delete);

        $users = $this->database
            ->table('users')
            ->all();

        $this->assertCount(3, $users);
    }

    public function testMagicCall()
    {
        $user = $this->database
            ->table('users')
            ->findByUsername('george');

        $this->assertSame('george', $user->username());
    }

    public function testFind()
    {
        $user = $this->database
            ->table('users')
            ->find(2);

        $this->assertSame('paul', $user->username());
    }

    public function testDistinct()
    {
        $users = $this->database
            ->table('users')
            ->distinct(true)
            ->select('password')
            ->all();

        // all passwords is same, query result count should one with distinct
        $this->assertCount(1, $users);
    }

    public function testMin()
    {
        $balance = $this->database
            ->table('users')
            ->min('balance');

        $this->assertSame((float)50, $balance);
    }

    public function testMax()
    {
        $balance = $this->database
            ->table('users')
            ->max('balance');

        $this->assertSame((float)200, $balance);
    }

    public function testPrimaryKeyName()
    {
        $user = $this->database
            ->table('users')
            ->primaryKeyName('username')
            ->find('paul');

        $this->assertSame('paul', $user->username());
    }

    public function testFirst()
    {
        $query = $this->database
            ->table('users')
            ->where([
                'username' => 'john'
            ]);

        $this->assertSame('John', $query->first()->fname());
        $this->assertSame('John', $query->row()->fname());
        $this->assertSame('John', $query->One()->fname());
    }

    public function testColumn()
    {
        $users = $this->database
            ->table('users')
            ->where([
                'role_id' => 3
            ])
            ->column('username');

        $this->assertInstanceOf('\Kirby\Toolkit\Collection', $users);
        $this->assertCount(2, $users->data());
        $this->assertSame(['george', 'mark'], $users->data());
    }

    public function testBindings()
    {
        $query = $this->database
            ->table('users')
            ->where('role_id = :role', ['role' => 3]);

        $this->assertSame(['role' => 3], $query->bindings());
    }

    public function testHaving()
    {
        $users = $this->database
            ->table('users')
            ->group('id')
            ->having('balance', '>', 50)
            ->all();

        $this->assertCount(3, $users);

        $users = $this->database
            ->table('users')
            ->group('id')
            ->having('balance', '<=', 100)
            ->all();

        $this->assertCount(2, $users);
    }

    public function testWhere()
    {
        // like 1
        $count = $this->database
            ->table('users')
            ->where('lname', 'like', '%Cart%')
            ->count();

        $this->assertSame(1, $count);

        // like 2
        $count = $this->database
            ->table('users')
            ->where('lname like ?', '%on')
            ->count();

        $this->assertSame(2, $count);

        // in
        $count = $this->database
            ->table('users')
            ->where('username', 'in', ['john', 'paul'])
            ->count();

        $this->assertSame(2, $count);

        // invalid predicate
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid predicate INV');

        $this->database
            ->table('users')
            ->where('username', 'INV', ['john', 'paul'])
            ->count();
    }

    public function testAndWhere()
    {
        $count = $this->database
            ->table('users')
            ->where([
                'role_id' => 3
            ])
            ->andWhere('balance > 50')
            ->count();

        $this->assertSame(1, $count);
    }

    public function testOrWhere()
    {
        $count = $this->database
            ->table('users')
            ->where([
                'role_id' => 1
            ])
            ->orWhere('balance <= 100')
            ->count();

        $this->assertSame(3, $count);
    }
}
