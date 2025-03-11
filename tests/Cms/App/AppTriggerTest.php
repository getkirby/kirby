<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversDefaultClass;

#[CoversDefaultClass(App::class)]
class AppTriggerTest extends TestCase
{
	public function testTriggerEvent()
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

		$this->app->trigger('test', ['value' => 10]);
	}

	public function testTriggerEventWithCustomEventObject()
	{
		$self        = $this;
		$customEvent = new Event('custom', ['value' => 10]);

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (Event $event) use ($self, $customEvent) {
					$self->assertSame($event, $customEvent);
					$self->assertSame('custom', $event->name());
					$self->assertSame(['value' => 10], $event->arguments());
				}
			]
		]);

		$this->app->trigger('test', [], $customEvent);
	}

	public function testTriggerWithMultipleParameters()
	{
		$self = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (int $a, int $b, int $c, Event $event) use ($self) {
					$self->assertCount(3, $event->arguments());
					$self->assertSame(5, $a);
					$self->assertSame(6, $b);
					$self->assertSame(4, $c);
				}
			]
		]);

		$this->app->trigger('test', ['a' => 5, 'b' => 6, 'c' => 4]);
	}

	public function testTriggerWithNestedTriggerCall()
	{
		$calls = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'a' => function () use (&$calls) {
					$calls++;
					$this->trigger('b');
				},
				'b' => function () use (&$calls) {
					$calls++;
				}
			]
		]);

		$this->app->trigger('a');

		$this->assertSame(2, $calls);
	}

	public function testTriggerWithNestedRecursiveTriggerCall()
	{
		$calls = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function () use (&$calls) {
					$calls++;

					// should not call the same hook again
					// to avoid infinite loops
					$this->trigger('test');
				}
			]
		]);

		$this->app->trigger('test');

		$this->assertSame(1, $calls);
	}

	public function testTriggerWithSingleParameter()
	{
		$self = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (int $value, Event $event) use ($self) {
					$self->assertCount(1, $event->arguments());
					$self->assertSame(5, $value);
				}
			]
		]);

		$this->app->trigger('test', ['value' => 5]);
	}

	public function testTriggerWithWildcard()
	{
		$self = $this;
		$calls = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'test.event:after' => [
					function (Event $event) use ($self, &$calls) {
						$self->assertSame('test.event:after', $event->name());
						$calls++;
					},
					function (Event $event) use ($self, &$calls) {
						$self->assertSame('test.event:after', $event->name());
						$calls++;
					}
				],
				'test.*:after' => [
					function (Event $event) use ($self, &$calls) {
						$self->assertSame('test.event:after', $event->name());
						$calls++;
					}
				],
				'test.event:*' => [
					function (Event $event) use ($self, &$calls) {
						$self->assertSame('test.event:after', $event->name());
						$calls++;
					}
				]
			]
		]);

		$this->app->trigger('test.event:after');
		$this->assertSame(4, $calls);
	}

	public function testTriggerWithoutArguments()
	{
		$self = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'test' => function (Event $event) use ($self) {
					$self->assertCount(0, $event->arguments());
				}
			]
		]);

		$this->app->trigger('test');
	}

	public function testTriggerWithoutHandler()
	{
		$this->assertNull($this->app->trigger('does-not-exist', ['value' => 10]));
	}
}
