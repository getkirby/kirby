<?php

namespace Kirby\Query;

use Closure;
use Kirby\Cms\App;

/**
 * @coversDefaultClass Kirby\Query\Query
 */
class QueryTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$query = Query::factory(' user.me ');
		$this->assertSame('user.me', $query->query);
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve()
	{
		$query = new Query("user.self.likes(['(', ')']).self.drink");
		$data  = ['user' => new TestUser()];
		$this->assertSame(['gin', 'tonic', 'cucumber'], $query->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithEmptyQuery()
	{
		$query = new Query('');
		$data = ['foo' => 'bar'];
		$this->assertSame($data, $query->resolve($data));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithExactArrayMatch()
	{
		$query = new Query('user');
		$this->assertSame('homer', $query->resolve(['user' => 'homer']));

		$query = new Query('user.username');
		$this->assertSame('homer', $query->resolve(['user.username' => 'homer']));

		$query = new Query('user.callback');
		$this->assertSame('homer', $query->resolve(['user.callback' => fn () => 'homer']));
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolveWithClosureArgument()
	{
		$query = new Query('foo.bar(() => foo.homer)');
		$data  = [
			'foo' => [
				'bar'   => fn ($callback) => $callback,
				'homer' => 'simpson'
			]
		];

		$bar = $query->resolve($data);
		$this->assertInstanceOf(Closure::class, $bar);
		$bar = $bar();
		$this->assertSame('simpson', $bar);
	}

	public function testDefaultEntryKirby()
	{
		$query = new Query('kirby');
		$this->assertInstanceOf(App::class, $query->resolve());
	}

	public function testDefaultFunctionT()
	{
		$query = new Query('t("add")');
		$this->assertSame('Add', $query->resolve());

		$query = new Query('t("notfound", "fallback")');
		$this->assertSame('fallback', $query->resolve());

		$query = new Query('t("add", null, "de")');
		$this->assertSame('Hinzufügen', $query->resolve());
	}
}
