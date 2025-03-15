<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(App::class)]
class AppApplyTest extends TestCase
{
	public function testApplyEvent()
	{
		$self = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (Event $event) use ($self) {
					$self->assertSame('test', $event->name());
					$self->assertSame(['value' => 10], $event->arguments());
				}
			]
		]);

		$this->app->apply('test', ['value' => 10], 'value');
	}

	public function testApplyEventWithCustomEventObject()
	{
		$self        = $this;
		$customEvent = new Event('test', ['value' => 10]);

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (Event $event) use ($self, $customEvent) {
					$self->assertSame($event, $customEvent);
					$self->assertSame(['value' => 10], $event->arguments());

					// should modify the value of the custom event
					// after the hook has been applied
					return 20;
				}
			]
		]);

		$this->app->apply('test', [], 'value', $customEvent);

		$this->assertSame(20, $customEvent->argument('value'), 'The custom event value should have been modified');
	}

	public function testApplyWithInvalidModifyArgument()
	{
		$this->app = $this->app->clone([
			'hooks' => [
				'test' => fn () => null
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The argument unused does not exist');

		$this->app->apply('test', ['foo' => 'bar'], 'unused');
	}

	public function testApplyWithMultipleParameters()
	{
		$self = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (int $a, int $b, int $c, Event $event) use ($self) {
					$self->assertCount(3, $event->arguments());
					$self->assertSame(5, $a);
					$self->assertSame(6, $b);
					$self->assertSame(4, $c);
					return $a + $b + $c;
				}
			]
		]);

		$this->assertSame(15, $this->app->apply('test', ['a' => 5, 'b' => 6, 'c' => 4], 'a'));
	}

	public function testApplyWithNestedApplyCall()
	{
		$calls = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'a' => function (string $value) use (&$calls) {
					$calls++;
					return $this->apply('b', ['value' => $value . 'a'], 'value');
				},
				'b' => function (string $value) use (&$calls) {
					$calls++;
					return $value . 'b';
				}
			]
		]);

		$this->assertSame('testab', $this->app->apply('a', ['value' => 'test'], 'value'));
		$this->assertSame(2, $calls);
	}

	public function testApplyWithNestedRecursiveApplyCall()
	{
		$calls = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (string $value) use (&$calls) {
					$calls++;

					// should add one more `test` but don't apply another
					// hook call to avoid infinite loops
					return $this->apply('test', ['value' => $value . 'test'], 'value');
				}
			]
		]);

		$this->assertSame('testtest', $this->app->apply('test', ['value' => 'test'], 'value'));
		$this->assertSame(1, $calls);
	}

	public function testApplyWithNullAsReturnValue()
	{
		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (int $value) {
					return null;
				}
			]
		]);

		$this->assertSame(10, $this->app->apply('test', ['value' => 10], 'value'));
	}

	public function testApplyWithSingleParameter()
	{
		$self = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (int $value, Event $event) use ($self) {
					$self->assertCount(1, $event->arguments());
					$self->assertSame(5, $value);
					return $value * 2;
				}
			]
		]);

		$this->assertSame(10, $this->app->apply('test', ['value' => 5], 'value'));
	}

	public function testApplyWithWildcard()
	{
		$self = $this;
		$calls = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'test.event:after' => [
					function (int $value, Event $event) use ($self, &$calls) {
						$self->assertSame('test.event:after', $event->name());
						$calls++;
						return $value + 1;
					},
					function (int $value, Event $event) use ($self, &$calls) {
						$self->assertSame('test.event:after', $event->name());
						$calls++;
						return $value + 2;
					}
				],
				'test.*:after' => [
					function (int $value, Event $event) use ($self, &$calls) {
						$self->assertSame('test.event:after', $event->name());
						$calls++;
						return $value + 3;
					}
				],
				'test.event:*' => [
					function (int $value, Event $event) use ($self, &$calls) {
						$self->assertSame('test.event:after', $event->name());
						$calls++;
						return $value + 4;
					}
				]
			]
		]);

		$this->assertSame(11, $this->app->apply('test.event:after', ['value' => 1], 'value'));
		$this->assertSame(4, $calls);
	}

	public function testApplyWithoutHandler()
	{
		$this->assertSame(10, $this->app->apply('does-not-exist', ['value' => 10], 'value'));
	}

	public function testApplyWithoutReturnValue()
	{
		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (int $value) {
					// don't return anything
				}
			]
		]);

		$this->assertSame(10, $this->app->apply('test', ['value' => 10], 'value'));
	}
}
