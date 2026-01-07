<?php

namespace Kirby\Reflection;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class ReflectableTestClass
{
	public function __construct(
		protected $a,
		protected $b
	) {
	}
}

class ReflectableTestChildClass extends ReflectableTestClass
{
	public function __construct(
		protected $c,
		...$props
	) {
		parent::__construct(...$props);
	}
}

#[CoversClass(Constructor::class)]
class ConstructorTest extends TestCase
{
	public function reflection(string $class = ReflectableTestClass::class): Constructor
	{
		return new Constructor($class);
	}

	public function testClassifyArguments(): void
	{
		$accepted = [
			'a' => 'Test A',
			'b' => 'Test B',
		];

		$ignored = [
			'c' => 'Test C'
		];

		$result = $this->reflection()->classifyArguments([
			...$accepted,
			...$ignored
		]);

		$this->assertSame($accepted, $result['accepted']);
		$this->assertSame($ignored, $result['ignored']);
	}

	public function testClassifyArgumentsWithNestedParameters(): void
	{
		$accepted = [
			'a' => 'Test A',
			'b' => 'Test B',
			'c' => 'Test C'
		];

		$result = $this->reflection(ReflectableTestChildClass::class)->classifyArguments([
			...$accepted,
		]);

		$this->assertSame($accepted, $result['accepted']);
		$this->assertSame([], $result['ignored']);
	}

	public function testGetAcceptedArguments(): void
	{
		$accepted = [
			'a' => 'Test A',
			'b' => 'Test B',
		];

		$ignored = [
			'c' => 'Test C'
		];

		$result = $this->reflection()->getAcceptedArguments($accepted);

		$this->assertSame($accepted, $result);

		$result = $this->reflection()->getAcceptedArguments([
			...$accepted,
			...$ignored
		]);

		$this->assertSame($accepted, $result);

		$result = $this->reflection()->getAcceptedArguments([]);

		$this->assertSame([], $result);

		$result = $this->reflection()->getAcceptedArguments($ignored);

		$this->assertSame([], $result);
	}

	public function testGetAcceptedArgumentsWithNestedParameters(): void
	{
		$accepted = [
			'a' => 'Test A',
			'b' => 'Test B',
			'c' => 'Test C'
		];

		$result = $this->reflection(ReflectableTestChildClass::class)->getAcceptedArguments($accepted);

		$this->assertSame($accepted, $result);
	}

	public function testGetAllParameters(): void
	{
		$parameters = $this->reflection()->getAllParameters();

		$this->assertCount(2, $parameters);

		$parameters = $this->reflection(ReflectableTestChildClass::class)->getAllParameters();

		$this->assertCount(3, $parameters);
	}

	public function testGetIgnoredArguments(): void
	{
		$accepted = [
			'a' => 'Test A',
			'b' => 'Test B',
		];

		$ignored = [
			'c' => 'Test C'
		];

		$result = $this->reflection()->getIgnoredArguments($ignored);

		$this->assertSame($ignored, $result);

		$result = $this->reflection()->getIgnoredArguments([
			...$accepted,
			...$ignored
		]);

		$this->assertSame($ignored, $result);

		$result = $this->reflection()->getIgnoredArguments([]);

		$this->assertSame([], $result);

		$result = $this->reflection()->getIgnoredArguments($accepted);

		$this->assertSame([], $result);
	}

	public function testGetIgnoredArgumentsWithNestedParameters(): void
	{
		$accepted = [
			'a' => 'Test A',
			'b' => 'Test B',
			'c' => 'Test C'
		];

		$ignored = [
			'd' => 'Test D'
		];

		$result = $this->reflection(ReflectableTestChildClass::class)->getIgnoredArguments($ignored);

		$this->assertSame($ignored, $result);
	}

	public function testGetParameterNames(): void
	{
		$result = $this->reflection()->getParameterNames();

		$this->assertSame(['a', 'b'], $result);
	}

	public function testGetParameterNamesWithNestedParameters(): void
	{
		$result = $this->reflection(ReflectableTestChildClass::class)->getParameterNames();

		$this->assertSame(['c', 'a', 'b'], $result);
	}

	public function testGetParentParameters(): void
	{
		$parameters = $this->reflection()->getParentParameters();

		$this->assertSame([], $parameters);

		$parameters = $this->reflection(ReflectableTestChildClass::class)->getParentParameters();

		$this->assertCount(2, $parameters);
	}
}
