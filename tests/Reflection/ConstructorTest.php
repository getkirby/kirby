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

#[CoversClass(Constructor::class)]
class ConstructorTest extends TestCase
{
	public function reflection(): Constructor
	{
		return new Constructor(ReflectableTestClass::class);
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
	}

	public function testParamsNames(): void
	{
		$result = $this->reflection()->getParameterNames();

		$this->assertSame(['a', 'b'], $result);
	}
}
