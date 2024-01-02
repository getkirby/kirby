<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Controller
 */
class ControllerTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	/**
	 * @covers ::call
	 */
	public function testCall()
	{
		$controller = new Controller(fn () => 'test');
		$this->assertSame('test', $controller->call());
	}

	/**
	 * @covers ::arguments
	 */
	public function testArguments()
	{
		$controller = new Controller(fn ($a, $b) => $a . $b);

		$this->assertSame('AB', $controller->call(null, [
			'a' => 'A',
			'b' => 'B'
		]));
	}

	/**
	 * @covers ::arguments
	 */
	public function testArgumentsOrder()
	{
		$controller = new Controller(fn ($b, $a) => $b . $a);

		$this->assertSame('BA', $controller->call(null, [
			'a' => 'A',
			'b' => 'B'
		]));
	}

	/**
	 * @covers ::arguments
	 */
	public function testVariadicArguments()
	{
		$controller = new Controller(fn ($c, ...$args) => $c . '/' . implode('', $args));

		$this->assertSame('C/AB', $controller->call(null, [
			'a' => 'A',
			'b' => 'B',
			'c' => 'C'
		]));
	}

	/**
	 * @covers ::call
	 */
	public function testCallBind()
	{
		$model = new Obj(['foo' => 'bar']);

		$controller = new Controller(fn () => $this);
		$this->assertSame($model, $controller->call($model));
	}

	/**
	 * @covers ::call
	 */
	public function testMissingParameter()
	{
		$controller = new Controller(fn ($a) => $a);
		$this->assertNull($controller->call());
	}

	/**
	 * @covers ::load
	 */
	public function testLoad()
	{
		$root       = static::FIXTURES . '/controller/controller.php';
		$controller = Controller::load($root);
		$this->assertSame('loaded', $controller->call());
	}

	/**
	 * @covers ::load
	 */
	public function testLoadNonExisting()
	{
		$root       = static::FIXTURES . '/controller/does-not-exist.php';
		$controller = Controller::load($root);
		$this->assertNull($controller);
	}

	/**
	 * @covers ::load
	 */
	public function testLoadInvalidController()
	{
		$root       = static::FIXTURES . '/controller/invalid.php';
		$controller = Controller::load($root);
		$this->assertNull($controller);
	}
}
