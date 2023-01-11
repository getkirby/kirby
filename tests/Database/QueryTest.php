<?php

namespace Kirby\Database;

use InvalidArgumentException;
use Kirby\Toolkit\Collection;
use PDOException;
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

		$this->database->table('users')->insert([
			'role_id'  => 4,
			'username' => 'foo',
			'fname'    => 'Mark',
			'lname'    => 'Bar',
			'email'    => 'foo@bar.com',
			'password' => 'AND',
			'balance'  => -30
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

	public function testLeftJoin()
	{
		$user = $this->database
			->table('users')
			->leftJoin('roles', 'roles.id = users.role_id')
			->where([
				'username' => 'john'
			])
			->first();

		$this->assertSame('admin', $user->role());
	}

	public function testRightJoin()
	{
		$query = $this->database
			->table('users')
			->rightJoin('roles', 'users.role_id = roles.id')
			->build('select');

		// the result of the query should be correct, but we cannot
		// test it directly against SQLite because it does not support
		// right joins
		$expected = 'SELECT * FROM "users" RIGHT JOIN "roles" ON users.role_id = roles.id';

		$this->assertSame($expected, $query['query']);
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

		$this->assertSame((float)470, $sum);
	}

	public function testAggregateAndDebug()
	{
		$result = $this->database
			->table('users')
			->debug(true)
			->aggregate('avg', 'balance');


		$this->assertArrayHasKey('query', $result);
		$this->assertArrayHasKey('bindings', $result);
		$this->assertArrayHasKey('options', $result);
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

		$this->assertCount(4, $users);
	}

	public function testMagicCall()
	{
		$user = $this->database
			->table('users')
			->findByUsername('george');

		$this->assertSame('george', $user->username());
	}

	public function testFail()
	{
		// should not throw an exception
		$this->database
			->table('users')
			->fail(false)
			->where('foo = "bar"')
			->one();

		$this->expectException(PDOException::class);
		$this->expectExceptionMessage('SQLSTATE[HY000]: General error: 1 no such column: foo');

		// should throw an exception
		$this->database
			->table('users')
			->fail(true)
			->where('foo = "bar"')
			->one();
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

		// there are two different passwords in use
		$this->assertCount(2, $users);
	}

	public function testMin()
	{
		$balance = $this->database
			->table('users')
			->min('balance');

		$this->assertSame((float)-30, $balance);
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

		$this->assertInstanceOf(Collection::class, $users);
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
			->having('balance', '<=', 70)
			->all();

		$this->assertCount(2, $users);
	}

	public function testWhere()
	{
		// numeric comparison
		$count = $this->database
			->table('users')
			->where('balance', '>', 100)
			->count();

		$this->assertSame(2, $count);

		// numeric comparison (value 0)
		$count = $this->database
			->table('users')
			->where('balance', '>', 0)
			->count();

		$this->assertSame(4, $count);

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

		// 'AND' as value
		$count = $this->database
			->table('users')
			->where('password', '=', 'AND')
			->count();

		$this->assertSame(1, $count);
	}

	public function testWhereInvalidPredicate()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Invalid predicate INV');

		$this->database
			->table('users')
			->where('username', 'INV', ['john', 'paul'])
			->count();
	}

	public function testWhereInvalidPredicateOperator()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Invalid predicate/operator <!>');

		$this->database
			->table('users')
			->where('username', '<!>', 'john')
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

		// with value 0
		$count = $this->database
			->table('users')
			->where([
				'role_id' => 3
			])
			->andWhere('balance', '>', 0)
			->count();

		$this->assertSame(2, $count);

		// 'AND' as value
		$count = $this->database
			->table('users')
			->where('fname', '=', 'Mark')
			->andWhere('password', '=', 'AND')
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

		$this->assertSame(4, $count);

		// with value 0
		$count = $this->database
			->table('users')
			->where([
				'role_id' => 1
			])
			->orWhere('balance', '<=', 0)
			->count();

		$this->assertSame(2, $count);

		// 'AND' as value
		$count = $this->database
			->table('users')
			->where('balance', '>=', 100)
			->orWhere('password', '=', 'AND')
			->count();

		$this->assertSame(4, $count);
	}

	public function testWhereCallback()
	{
		$count = $this->database
			->table('users')
			->where('balance', '>', 75)
			->where(fn ($q) => $q->where('role_id', '=', 3))
			->count();

		$this->assertSame(1, $count);
	}

	public function testPage()
	{
		$query = $this->database->table('users');

		// example one
		$results = $query->page(1, 10);
		$pagination = $results->pagination();

		$this->assertCount(5, $results);
		$this->assertSame('John', $results->first()->fname());
		$this->assertTrue(get_class($pagination) === 'Kirby\Toolkit\Pagination');
		$this->assertSame(1, $pagination->pages());
		$this->assertSame(5, $pagination->total());
		$this->assertSame(1, $pagination->page());
		$this->assertSame(1, $pagination->start());
		$this->assertSame(5, $pagination->end());
		$this->assertSame(10, $pagination->limit());

		// example two
		$results = $query->page(3, 1);
		$pagination = $results->pagination();

		$this->assertCount(1, $results);
		$this->assertSame('George', $results->first()->fname());
		$this->assertTrue(get_class($pagination) === 'Kirby\Toolkit\Pagination');
		$this->assertSame(5, $pagination->pages());
		$this->assertSame(5, $pagination->total());
		$this->assertSame(3, $pagination->page());
		$this->assertSame(3, $pagination->start());
		$this->assertSame(3, $pagination->end());
		$this->assertSame(1, $pagination->limit());

		// example three
		$results = $query->page(2, 3);
		$pagination = $results->pagination();

		$this->assertCount(2, $results);
		$this->assertSame('Mark', $results->first()->fname());
		$this->assertTrue(get_class($pagination) === 'Kirby\Toolkit\Pagination');
		$this->assertSame(2, $pagination->pages());
		$this->assertSame(5, $pagination->total());
		$this->assertSame(2, $pagination->page());
		$this->assertSame(4, $pagination->start());
		$this->assertSame(5, $pagination->end());
		$this->assertSame(3, $pagination->limit());
	}

	public function testTable()
	{
		// should not throw an exception
		$this->database->table('users');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid table: accounts');

		// should throw an exception
		$this->database->table('accounts');
	}
}
