<?php

namespace Kirby\Cms;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Events::class)]
class EventsTest extends TestCase
{
	protected function app(array $hooks): App
	{
		return new App([
			'hooks' => $hooks
		]);
	}

	protected function events(array $hooks): Events
	{
		return new Events($this->app($hooks));
	}

	public function testApply(): void
	{
		$events = $this->events([
			'test' => [
				fn (string $message) => 'message: ' . $message
			]
		]);

		$result = $events->apply('test', ['message' => 'hello']);
		$this->assertSame('message: hello', $result);
	}

	public function testApplyAfterException(): void
	{
		$count  = 0;
		$events = $this->events([
			'test' => [
				function () use (&$count) {
					$count++;
					throw new Exception('blocked');
				}
			]
		]);

		foreach (['a', 'b'] as $message) {
			try {
				$events->apply('test', ['message' => $message]);
			} catch (Exception) {
			}
		}

		$this->assertSame(2, $count);
	}

	public function testApplyNesting(): void
	{
		$events = $this->events([
			'a' => [
				fn (string $message) => 'a: ' . $this->apply('b', ['message' => $message])
			],
			'b' => [
				fn (string $message) => 'b: ' . $message
			]
		]);

		$result = $events->apply('a', ['message' => 'hello']);

		$this->assertSame('a: b: hello', $result);
	}

	public function testApplyWithLoopProtection(): void
	{
		$app = $this->app([
			'test' => [
				fn (string $message) => 'message: ' . $this->apply('test', ['message' => $message])
			]
		]);

		$result = $app->apply('test', ['message' => 'hello']);
		$this->assertSame('message: hello', $result);
	}

	public function testApplyWithWildcard(): void
	{
		$events = $this->events([
			'test.*' => [
				fn (string $message) => 'message: ' . $message
			]
		]);

		$a = $events->apply('test.a', ['message' => 'hello']);
		$b = $events->apply('test.b', ['message' => 'hello']);

		$this->assertSame('message: hello', $a);
		$this->assertSame('message: hello', $b);
	}

	public function testApplyWithoutHooks(): void
	{
		$events = $this->events([]);

		$event  = new Event('test');
		$this->assertSame([], $events->hooks($event));

		$result = $events->apply('test', ['message' => 'hello']);
		$this->assertSame('hello', $result);
	}

	public function testApplyWithoutModifier(): void
	{
		$events = $this->events([
			'test' => [
				fn ($a, $b) => $a . ' ' . $b,
				fn ($a, $b) => $b . ' ' . $a
			]
		]);

		$result = $events->apply('test', ['a' => 'hello', 'b' => 'world']);
		$this->assertSame('world hello world', $result);
	}

	public function testApplyWithModifier(): void
	{
		$events = $this->events([
			'test' => [
				fn ($a, $b) => $a . ' ' . $b,
				fn ($a, $b) => $b . ' ' . $a
			]
		]);

		$result = $events->apply('test', ['a' => 'hello', 'b' => 'world'], 'b');
		$this->assertSame('hello world hello', $result);
	}

	public function testHooksWithSingleHandler(): void
	{
		$events = $this->events([
			'test' => $handlers = [
				fn () => 'test'
			]
		]);

		$event = new Event('test');
		$this->assertSame($handlers, $events->hooks($event));
	}

	public function testHooksWithMultipleHandlers(): void
	{
		$events = $this->events([
			'test' => $handlers = [
				fn () => 'a',
				fn () => 'b'
			]
		]);

		$event = new Event('test');
		$this->assertSame($handlers, $events->hooks($event));
	}

	public function testHooksWithWildcards(): void
	{
		$events = $this->events([
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
		]);

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

	public function testTrigger(): void
	{
		$count = 0;
		$events = $this->events([
			'test' => [
				function () use (&$count) {
					$count++;
				}
			]
		]);

		$events->trigger('test');
		$this->assertSame(1, $count);
	}

	public function testTriggerAfterException(): void
	{
		$a      = 0;
		$b      = 0;
		$events = $this->events([
			'a' => [
				function () use (&$a) {
					$a++;
					throw new Exception('blocked');
				}
			],
			'b' => [
				function () use (&$b) {
					$b++;
				}
			]
		]);

		foreach ([1, 2] as $attempt) {
			try {
				$events->trigger('a');
			} catch (Exception) {
			}
		}

		// the loop protection must not outlive the failed hook
		$events->trigger('b');
		$events->trigger('b');

		$this->assertSame(2, $a);
		$this->assertSame(2, $b);
	}

	public function testTriggerNesting(): void
	{
		$message = '';
		$events = $this->events([
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
		]);

		$events->trigger('a');
		$this->assertSame('ab', $message);
	}

	public function testTriggerNestingRepeated(): void
	{
		$message = '';
		$app     = $this->app([
			'a' => [
				function () use (&$message) {
					$message .= 'a';
					$this->trigger('b');
					$this->trigger('b');
				}
			],
			'b' => [
				function () use (&$message) {
					$message .= 'b';
				}
			]
		]);

		$app->trigger('a');
		$this->assertSame('abb', $message);
	}

	public function testTriggerNestingSameEvent(): void
	{
		$message = '';
		$app     = $this->app([
			'a' => [
				function () use (&$message) {
					$message .= '1';
				},
				function () use (&$message) {
					$message .= '2';
					$this->trigger('a');
				}
			]
		]);

		// the nested event runs the first hook again,
		// only the running second hook is skipped
		$app->trigger('a');
		$this->assertSame('121', $message);
	}

	public function testTriggerWithLoopProtection(): void
	{
		$count = 0;
		$app   = $this->app([
			'test' => [
				function () use (&$count) {
					$count++;
					$this->trigger('test');
				}
			]
		]);

		$app->trigger('test');
		$this->assertSame(1, $count);
	}

	public function testTriggerWithMutualLoopProtection(): void
	{
		$message = '';
		$app     = $this->app([
			'a' => [
				function () use (&$message) {
					$message .= 'a';
					$this->trigger('b');
				}
			],
			'b' => [
				function () use (&$message) {
					$message .= 'b';
					$this->trigger('a');
				}
			]
		]);

		$app->trigger('a');
		$this->assertSame('ab', $message);
	}

	public function testTriggerWithWildcard(): void
	{
		$count  = 0;
		$events = $this->events([
			'test.*' => [
				function () use (&$count) {
					$count++;
				}
			]
		]);

		$events->trigger('test.a');
		$events->trigger('test.b');

		$this->assertSame(2, $count);
	}

	public function testTriggerWithBoundApp(): void
	{
		$self   = $this;
		$events = $this->events([
			'test' => [
				function () use ($self) {
					$self->assertInstanceOf(App::class, $this);
				}
			]
		]);

		$events->trigger('test');
	}

	public function testTriggerWithMultipleHandlers(): void
	{
		$count = 0;
		$events = $this->events([
			'test' => [
				function () use (&$count) {
					$count++;
				},
				function () use (&$count) {
					$count++;
				}
			]
		]);

		$events->trigger('test');

		$this->assertSame(2, $count);
	}
}
