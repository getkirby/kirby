<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Controller::class)]
class ControllerTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function testArguments()
	{
		$controller = new Controller(fn ($a, $b) => $a . $b);

		$this->assertSame('AB', $controller->call(null, [
			'a' => 'A',
			'b' => 'B'
		]));
	}

	public function testArgumentsOrder()
	{
		$controller = new Controller(fn ($b, $a) => $b . $a);

		$this->assertSame('BA', $controller->call(null, [
			'a' => 'A',
			'b' => 'B'
		]));
	}

	public function testArgumentsVariadic()
	{
		$controller = new Controller(fn ($c, ...$args) => $c . '/' . implode('', $args));

		$this->assertSame('C/AB', $controller->call(null, [
			'a' => 'A',
			'b' => 'B',
			'c' => 'C'
		]));
	}

	public function testArgumentsNoDefaultNull()
	{
		$controller = new Controller(fn ($a, $b = 'foo') => ($a === null ? 'null' : $a) . ($b === null ? 'null' : $b));

		$this->assertSame('nullfoo', $controller->call());
	}

	public function testCall()
	{
		$controller = new Controller(fn () => 'test');
		$this->assertSame('test', $controller->call());
	}

	public function testCallBind()
	{
		$model = new Obj(['foo' => 'bar']);

		$controller = new Controller(fn () => $this);
		$this->assertSame($model, $controller->call($model));
	}

	public function testCallMissingParameter()
	{
		$controller = new Controller(fn ($a) => $a);
		$this->assertNull($controller->call());
	}

	public function testLoad()
	{
		$root       = static::FIXTURES . '/controller/controller.php';
		$controller = Controller::load($root);
		$this->assertSame('loaded', $controller->call());
	}

	public function testLoadNonExisting()
	{
		$root       = static::FIXTURES . '/controller/does-not-exist.php';
		$controller = Controller::load($root);
		$this->assertNull($controller);
	}

	public function testLoadInvalidController()
	{
		$root       = static::FIXTURES . '/controller/invalid.php';
		$controller = Controller::load($root);
		$this->assertNull($controller);
	}
}
