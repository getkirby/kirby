<?php

namespace Kirby\Database;

use InvalidArgumentException;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Pagination;
use PDOException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Query::class)]
class QueryTest extends TestCase
{
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
            "balance" INTEGER,
			"active" INTEGER NOT NULL
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
			'balance'  => 200,
			'active'   => true,
		]);

		$this->database->table('users')->insert([
			'role_id'  => 2,
			'username' => 'paul',
			'fname'    => 'Paul',
			'lname'    => 'McCartney',
			'email'    => 'paul@test.com',
			'password' => 'beatles',
			'balance'  => 150,
			'active'   => true,
		]);

		$this->database->table('users')->insert([
			'role_id'  => 3,
			'username' => 'george',
			'fname'    => 'George',
			'lname'    => 'Harrison',
			'email'    => 'george@test.com',
			'password' => 'beatles',
			'balance'  => 100,
			'active'   => false,
		]);

		$this->database->table('users')->insert([
			'role_id'  => 3,
			'username' => 'mark',
			'fname'    => 'Mark',
			'lname'    => 'Otto',
			'email'    => 'mark@test.com',
			'password' => 'beatles',
			'balance'  => 50,
			'active'   => true,
		]);

		$this->database->table('users')->insert([
			'role_id'  => 4,
			'username' => 'foo',
			'fname'    => 'Mark',
			'lname'    => 'Bar',
			'email'    => 'foo@bar.com',
			'password' => 'AND',
			'balance'  => -30,
			'active'   => false,
		]);
	}

	public function testJoin(): void
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

	public function testInnerJoin(): void
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

	public function testLeftJoin(): void
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

	public function testRightJoin(): void
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

	public function testOrder(): void
	{
		$user = $this->database
			->table('users')
			->order('username desc')
			->first();

		$this->assertSame('paul', $user->username());
	}

	public function testGroup(): void
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

	public function testSum(): void
	{
		$sum = $this->database
			->table('users')
			->sum('balance');

		$this->assertSame((float)470, $sum);
	}

	public function testAggregateAndDebug(): void
	{
		$result = $this->database
			->table('users')
			->debug(true)
			->aggregate('avg', 'balance');


		$this->assertArrayHasKey('query', $result);
		$this->assertArrayHasKey('bindings', $result);
		$this->assertArrayHasKey('options', $result);
	}

	public function testAvg(): void
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

	public function testCount(): void
	{
		$count = $this->database
			->table('users')
			->where([
				'role_id' => 3
			])
			->count();

		$this->assertSame(2, $count);
	}

	public function testQuery(): void
	{
		$result = $this->database
			->query('SELECT * FROM users WHERE role_id = :role', ['role' => 3]);

		$this->assertCount(2, $result->data());
	}

	public function testUpdate(): void
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

	public function testDelete(): void
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

	public function testMagicCall(): void
	{
		$user = $this->database
			->table('users')
			->findByUsername('george');

		$this->assertSame('george', $user->username());
	}

	public function testFail(): void
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

	public function testFetch(): void
	{
		$query = $this->database
			->table('users')
			->where([
				'username' => 'john'
			])
			->fetch(fn ($row) => $row['fname']);

		$this->assertSame('John', (clone $query)->limit(1)->all()->first());
		$this->assertSame('John', (clone $query)->first());
		$this->assertSame('John', (clone $query)->row());

		$falseQuery = $this->database
			->table('users')
			->where([
				'username' => 'john-lennon-and-beatles'
			])
			->fetch(fn ($row) => $row['fname']);

		$this->assertNull((clone $falseQuery)->limit(1)->all()->first());
		$this->assertSame(false, (clone $falseQuery)->first());
		$this->assertSame(false, (clone $falseQuery)->row());

		$query = $this->database
			->table('users')
			->where(['username' => 'john']);

		$x = fn ($row, $key) => $row['fname'] . ' ' . $row['lname'];

		$this->assertSame('John Lennon', (clone $query)->fetch($x)->first());
		$this->assertSame('John Lennon', (clone $query)->fetch([$this, 'fetchTestCallable'])->first());
		$this->assertInstanceOf(
			MockClassWithCallable::class,
			(clone $query)->fetch([MockClassWithCallable::class, 'fromDb'])->first()
		);
		$this->assertSame(
			'John Lennon',
			(clone $query)->fetch('\Kirby\Database\MockClassWithCallable::fromDb')->first()->name()
		);
	}

	/**
	 * Helper function for testFetch()
	 */
	public function fetchTestCallable(array $row, $key = null)
	{
		return $row['fname'] . ' ' . $row['lname'];
	}

	public function testFind(): void
	{
		$user = $this->database
			->table('users')
			->find(2);

		$this->assertSame('paul', $user->username());
	}

	public function testDistinct(): void
	{
		$users = $this->database
			->table('users')
			->distinct(true)
			->select('password')
			->all();

		// there are two different passwords in use
		$this->assertCount(2, $users);
	}

	public function testMin(): void
	{
		$balance = $this->database
			->table('users')
			->min('balance');

		$this->assertSame((float)-30, $balance);
	}

	public function testMax(): void
	{
		$balance = $this->database
			->table('users')
			->max('balance');

		$this->assertSame((float)200, $balance);
	}

	public function testPrimaryKeyName(): void
	{
		$user = $this->database
			->table('users')
			->primaryKeyName('username')
			->find('paul');

		$this->assertSame('paul', $user->username());
	}

	public function testFirst(): void
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

	public function testColumn(): void
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

	public function testBindings(): void
	{
		$query = $this->database
			->table('users')
			->where('role_id = :role', ['role' => 3]);

		$this->assertSame(['role' => 3], $query->bindings());
	}

	public function testHaving(): void
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

	public function testWhere(): void
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

		// numeric comparison (2 arguments)
		$count = $this->database
			->table('users')
			->where('balance > ?', 0)
			->count();

		$this->assertSame(4, $count);

		// boolean comparison
		$count = $this->database
			->table('users')
			->where('active = ?', [false])
			->count();

		$this->assertSame(2, $count);

		// boolean comparison
		$count = $this->database
			->table('users')
			->where('active', '=', false)
			->count();

		$this->assertSame(2, $count);

		// boolean comparison (2 arguments)
		$count = $this->database
			->table('users')
			->where('active = ?', false)
			->count();

		$this->assertSame(2, $count);

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

		// between
		$count = $this->database
			->table('users')
			->where('balance', 'between', [100, 200])
			->count();

		$this->assertSame(3, $count);

		// between (with strings)
		$count = $this->database
			->table('users')
			->where('username', 'between', ['george', 'mark'])
			->count();

		$this->assertSame(3, $count);

		// 'AND' as value
		$count = $this->database
			->table('users')
			->where('password', '=', 'AND')
			->count();

		$this->assertSame(1, $count);
	}

	public function testWhereInvalidPredicate(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid predicate INV');

		$this->database
			->table('users')
			->where('username', 'INV', ['john', 'paul'])
			->count();
	}

	public function testWhereInvalidPredicateOperator(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid predicate/operator <!>');

		$this->database
			->table('users')
			->where('username', '<!>', 'john')
			->count();
	}

	public function testAndWhere(): void
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

	public function testOrWhere(): void
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

	public function testWhereCallback(): void
	{
		$count = $this->database
			->table('users')
			->where('balance', '>', 75)
			->where(fn ($q) => $q->where('role_id', '=', 3))
			->count();

		$this->assertSame(1, $count);
	}

	public function testPage(): void
	{
		$query = $this->database->table('users');

		// example one
		$results = $query->page(1, 10);
		$pagination = $results->pagination();

		$this->assertCount(5, $results);
		$this->assertSame('John', $results->first()->fname());
		$this->assertTrue($pagination instanceof Pagination);
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
		$this->assertTrue($pagination instanceof Pagination);
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
		$this->assertTrue($pagination instanceof Pagination);
		$this->assertSame(2, $pagination->pages());
		$this->assertSame(5, $pagination->total());
		$this->assertSame(2, $pagination->page());
		$this->assertSame(4, $pagination->start());
		$this->assertSame(5, $pagination->end());
		$this->assertSame(3, $pagination->limit());
	}

	public function testTable(): void
	{
		// should not throw an exception
		$this->database->table('users');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid table: accounts');

		// should throw an exception
		$this->database->table('accounts');
	}
}
