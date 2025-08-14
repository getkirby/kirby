<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Controller::class)]
class ControllerTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';

	public function testArguments(): void
	{
		$controller = new Controller(fn ($a, $b) => $a . $b);

		$this->assertSame('AB', $controller->call(null, [
			'a' => 'A',
			'b' => 'B'
		]));
	}

	public function testArgumentsOrder(): void
	{
		$controller = new Controller(fn ($b, $a) => $b . $a);

		$this->assertSame('BA', $controller->call(null, [
			'a' => 'A',
			'b' => 'B'
		]));
	}

	public function testArgumentsVariadic(): void
	{
		$controller = new Controller(fn ($c, ...$args) => $c . '/' . implode('', $args));

		$this->assertSame('C/AB', $controller->call(null, [
			'a' => 'A',
			'b' => 'B',
			'c' => 'C'
		]));
	}

	public function testArgumentsNoDefaultNull(): void
	{
		$controller = new Controller(fn ($a, $b = 'foo') => ($a === null ? 'null' : $a) . ($b === null ? 'null' : $b));

		$this->assertSame('nullfoo', $controller->call());
	}

	public function testCall(): void
	{
		$controller = new Controller(fn () => 'test');
		$this->assertSame('test', $controller->call());
	}

	public function testCallBind(): void
	{
		$model = new Obj(['foo' => 'bar']);

		$controller = new Controller(fn () => $this);
		$this->assertSame($model, $controller->call($model));
	}

	public function testCallMissingParameter(): void
	{
		$controller = new Controller(fn ($a) => $a);
		$this->assertNull($controller->call());
	}

	public function testLoad(): void
	{
		$root       = static::FIXTURES . '/controller/controller.php';
		$controller = Controller::load($root);
		$this->assertSame('loaded', $controller->call());
	}

	public function testLoadNonExisting(): void
	{
		$root       = static::FIXTURES . '/controller/does-not-exist.php';
		$controller = Controller::load($root);
		$this->assertNull($controller);
	}

	public function testLoadInvalidController(): void
	{
		$root       = static::FIXTURES . '/controller/invalid.php';
		$controller = Controller::load($root);
		$this->assertNull($controller);
	}
}
