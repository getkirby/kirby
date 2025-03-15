<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Events::class)]
class EventsTest extends TestCase
{
	public function testApply(): void
	{
		$events = new Events(
			hooks: [
				'test' => [
					fn (string $message) => 'message: ' . $message
				]
			]
		);

		$result = $events->apply('test', ['message' => 'hello']);
		$this->assertSame('message: hello', $result);
	}

	public function testApplyNesting(): void
	{
		$events = new Events(
			hooks: [
				'a' => [
					fn (string $message) => 'a: ' . $this->apply('b', ['message' => $message])
				],
				'b' => [
					fn (string $message) => 'b: ' . $message
				]
			]
		);

		$result = $events->apply('a', ['message' => 'hello']);

		$this->assertSame('a: b: hello', $result);
	}

	public function testApplyWithLoopProtection(): void
	{
		$events = new Events(
			hooks: [
				'test' => [
					fn (string $message) => 'message: ' . $this->apply('test', ['message' => $message])
				]
			]
		);

		$result = $events->apply('test', ['message' => 'hello']);

		$this->assertSame('message: hello', $result);
	}

	public function testApplyWithWildcard(): void
	{
		$events = new Events(
			hooks: [
				'test.*' => [
					fn (string $message) => 'message: ' . $message
				]
			]
		);

		$a = $events->apply('test.a', ['message' => 'hello']);
		$b = $events->apply('test.b', ['message' => 'hello']);

		$this->assertSame('message: hello', $a);
		$this->assertSame('message: hello', $b);
	}

	public function testApplyWithBoundApp(): void
	{
		$self  = $this;
		$events = new Events(
			bind: $app = $this->app,
			hooks: [
				'test' => [
					function () use ($self, $app) {
						$self->assertSame($this, $app);
					}
				]
			]
		);

		$events->apply('test', ['message' => 'hello']);
	}

	public function testApplyWithoutHooks(): void
	{
		$events = new Events(
			hooks: []
		);

		$result = $events->apply('test', ['message' => 'hello']);

		$this->assertSame('hello', $result);
	}

	public function testApplyWithoutModifier(): void
	{
		$events = new Events(
			hooks: [
				'test' => [
					fn ($a, $b) => $a . ' ' . $b,
					fn ($a, $b) => $b . ' ' . $a
				]
			]
		);

		$result = $events->apply('test', ['a' => 'hello', 'b' => 'world']);
		$this->assertSame('world hello world', $result);
	}

	public function testApplyWithModifier(): void
	{
		$events = new Events(
			hooks: [
				'test' => [
					fn ($a, $b) => $a . ' ' . $b,
					fn ($a, $b) => $b . ' ' . $a
				]
			]
		);

		$result = $events->apply('test', ['a' => 'hello', 'b' => 'world'], 'b');
		$this->assertSame('hello world hello', $result);
	}

	public function testHooksWithSingleHandler(): void
	{
		$events = new Events(
			hooks: [
				'test' => $handlers = [
					fn () => 'test'
				]
			]
		);

		$event = new Event('test');
		$this->assertSame($handlers, $events->hooks($event));
	}

	public function testHooksWithMultipleHandlers(): void
	{
		$events = new Events(
			hooks: [
				'test' => $handlers = [
					fn () => 'a',
					fn () => 'b'
				]
			]
		);

		$event = new Event('test');
		$this->assertSame($handlers, $events->hooks($event));
	}

	public function testHooksWithWildcards(): void
	{
		$events = new Events(
			hooks: [
				'type.action:state' => $typeAndActionAndState = [
					fn () => 'test'
				],
				'type.action:*' => $typeAndAction = [
					fn () => 'test'
				],
				'type.*:*' => $type = [
					fn () => 'test'
				],
				'*' => $any = [
					fn () => 'test'
				]
			]
		);

		// full match
		$event = new Event('type.action:state');
		$this->assertSame([
			...$typeAndActionAndState,
			...$typeAndAction,
			...$type,
			...$any
		], $events->hooks($event));

		// no state match
		$event = new Event('type.action:differentState');
		$this->assertSame([
			...$typeAndAction,
			...$type,
			...$any
		], $events->hooks($event));

		// no action and no state match
		$event = new Event('type.differentAction:differentState');
		$this->assertSame([
			...$type,
			...$any
		], $events->hooks($event));

		// no match
		$event = new Event('differentType.differentAction:differentState');
		$this->assertSame([
			...$any
		], $events->hooks($event));
	}

	public function testHooksWithoutHandlers(): void
	{
		$events = new Events();
		$event  = new Event('test');
		$this->assertSame([], $events->hooks($event));
	}

	public function testTrigger(): void
	{
		$count = 0;
		$events = new Events(
			hooks: [
				'test' => [
					function () use (&$count) {
						$count++;
					}
				]
			]
		);

		$events->trigger('test');
		$this->assertSame(1, $count);
	}

	public function testTriggerNesting(): void
	{
		$message = '';
		$events = new Events(
			hooks: [
				'a' => [
					function () use (&$message) {
						$message .= 'a';
						$this->trigger('b');
					}
				],
				'b' => [
					function () use (&$message) {
						$message .= 'b';
					}
				]
			]
		);

		$events->trigger('a');

		$this->assertSame('ab', $message);
	}

	public function testTriggerWithLoopProtection(): void
	{
		$count = 0;
		$events = new Events(
			hooks: [
				'test' => [
					function () use (&$count) {
						$count++;
						$this->trigger('test');
					}
				]
			]
		);

		$events->trigger('test');
		$this->assertSame(1, $count);
	}

	public function testTriggerWithWildcard(): void
	{
		$count = 0;
		$events = new Events(
			hooks: [
				'test.*' => [
					function () use (&$count) {
						$count++;
					}
				]
			]
		);

		$events->trigger('test.a');
		$events->trigger('test.b');

		$this->assertSame(2, $count);
	}

	public function testTriggerWithBoundApp(): void
	{
		$self  = $this;
		$events = new Events(
			bind: $app = $this->app,
			hooks: [
				'test' => [
					function () use ($self, $app) {
						$self->assertSame($this, $app);
					}
				]
			]
		);

		$events->trigger('test');
	}

	public function testTriggerWithMultipleHandlers(): void
	{
		$count = 0;
		$events = new Events(
			hooks: [
				'test' => [
					function () use (&$count) {
						$count++;
					},
					function () use (&$count) {
						$count++;
					}
				]
			]
		);

		$events->trigger('test');

		$this->assertSame(2, $count);
	}
}
