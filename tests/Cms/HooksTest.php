<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Hooks::class)]
class HooksTest extends TestCase
{
	public function testApply()
	{
		$hooks = new Hooks(
			hooks: [
				'test' => [
					function (string $message) {
						return 'message: ' . $message;
					}
				]
			]
		);

		$result = $hooks->apply(new Event('test', ['message' => 'hello']));

		$this->assertSame('message: hello', $result);
	}

	public function testApplyNesting()
	{
		$hooks = new Hooks(
			hooks: [
				'a' => [
					function (string $message) {
						return 'a: ' . $this->apply(new Event('b', ['message' => $message]));
					}
				],
				'b' => [
					function (string $message) {
						return 'b: ' . $message;
					}
				]
			]
		);

		$result = $hooks->apply(new Event('a', ['message' => 'hello']));

		$this->assertSame('a: b: hello', $result);
	}

	public function testApplyWithLoopProtection()
	{
		$hooks = new Hooks(
			hooks: [
				'test' => [
					function (string $message) {
						return 'message: ' . $this->apply(new Event('test', ['message' => $message]));
					}
				]
			]
		);

		$result = $hooks->apply(new Event('test', ['message' => 'hello']));

		$this->assertSame('message: hello', $result);
	}

	public function testApplyWithWildcard()
	{
		$hooks = new Hooks(
			hooks: [
				'test.*' => [
					function (string $message) {
						return 'message: ' . $message;
					}
				]
			]
		);

		$a = $hooks->apply(new Event('test.a', ['message' => 'hello']));
		$b = $hooks->apply(new Event('test.b', ['message' => 'hello']));

		$this->assertSame('message: hello', $a);
		$this->assertSame('message: hello', $b);
	}

	public function testApplyWithBoundApp()
	{
		$self  = $this;
		$hooks = new Hooks(
			bind: $app = $this->app,
			hooks: [
				'test' => [
					function () use ($self, $app) {
						$self->assertSame($this, $app);
					}
				]
			]
		);

		$hooks->apply(new Event('test', ['message' => 'hello']));
	}

	public function testApplyWithoutHooks()
	{
		$hooks = new Hooks(
			hooks: []
		);

		$result = $hooks->apply(new Event('test', ['message' => 'hello']));

		$this->assertSame('hello', $result);
	}

	public function testApplyWithoutModifier()
	{
		$hooks = new Hooks(
			hooks: [
				'test' => [
					function ($a, $b) {
						return 'message: ' . $a . ' ' . $b;
					}
				]
			]
		);

		$name = 'test';
		$args = ['a' => 'hello', 'b' => 'world'];

		$event  = new Event($name, $args);
		$result = $hooks->apply($event);

		$this->assertSame('message: hello world', $result);
		$this->assertSame('message: hello world', $event->argument('a'), 'The first argument should have been modified by default');
	}

	public function testApplyWithModifier()
	{
		$hooks = new Hooks(
			hooks: [
				'test' => [
					function ($a, $b) {
						return 'message: ' . $a . ' ' . $b;
					}
				]
			]
		);

		$name = 'test';
		$args = ['a' => 'hello', 'b' => 'world'];

		// the custom event is needed to test the modified argument
		$event  = new Event($name, $args);
		$result = $hooks->apply($event, 'b');

		$this->assertSame('message: hello world', $result);
		$this->assertSame('message: hello world', $event->argument('b'), 'The given argument should have been modified');
	}

	public function testHooksWithSingleHandler()
	{
		$hooks = new Hooks(
			hooks: [
				'test' => $handlers = [
					function () {
						return 'test';
					}
				]
			]
		);

		$this->assertSame($handlers, $hooks->hooks(new Event('test')));
	}

	public function testHooksWithMultipleHandlers()
	{
		$hooks = new Hooks(
			hooks: [
				'test' => $handlers = [
					function () {
						return 'a';
					},
					function () {
						return 'b';
					}
				]
			]
		);

		$this->assertSame($handlers, $hooks->hooks(new Event('test')));
	}

	public function testHooksWithWildcards()
	{
		$hooks = new Hooks(
			hooks: [
				'type.action:state' => $typeAndActionAndState = [
					function () {
						return 'test';
					}
				],
				'type.action:*' => $typeAndAction = [
					function () {
						return 'test';
					}
				],
				'type.*:*' => $type = [
					function () {
						return 'test';
					}
				],
				'*' => $any = [
					function () {
						return 'test';
					}
				]
			]
		);

		// full match
		$this->assertSame([
			...$typeAndActionAndState,
			...$typeAndAction,
			...$type,
			...$any
		], $hooks->hooks(new Event('type.action:state')));

		// no state match
		$this->assertSame([
			...$typeAndAction,
			...$type,
			...$any
		], $hooks->hooks(new Event('type.action:differentState')));

		// no action and no state match
		$this->assertSame([
			...$type,
			...$any
		], $hooks->hooks(new Event('type.differentAction:differentState')));

		// no match
		$this->assertSame([
			...$any
		], $hooks->hooks(new Event('differentType.differentAction:differentState')));
	}

	public function testHooksWithoutHandlers()
	{
		$hooks = new Hooks(
			hooks: []
		);

		$this->assertSame([], $hooks->hooks(new Event('test')));
	}

	public function testTrigger()
	{
		$count = 0;
		$hooks = new Hooks(
			hooks: [
				'test' => [
					function () use (&$count) {
						$count++;
					}
				]
			]
		);

		$hooks->trigger(new Event('test'));

		$this->assertSame(1, $count);
	}

	public function testTriggerNesting()
	{
		$message = '';
		$hooks = new Hooks(
			hooks: [
				'a' => [
					function () use (&$message) {
						$message .= 'a';
						$this->trigger(new Event('b'));
					}
				],
				'b' => [
					function () use (&$message) {
						$message .= 'b';
					}
				]
			]
		);

		$hooks->trigger(new Event('a'));

		$this->assertSame('ab', $message);
	}

	public function testTriggerWithLoopProtection()
	{
		$count = 0;
		$hooks = new Hooks(
			hooks: [
				'test' => [
					function () use (&$count) {
						$count++;
						$this->trigger(new Event('test'));
					}
				]
			]
		);

		$hooks->trigger(new Event('test'));
		$this->assertSame(1, $count);
	}

	public function testTriggerWithWildcard()
	{
		$count = 0;
		$hooks = new Hooks(
			hooks: [
				'test.*' => [
					function () use (&$count) {
						$count++;
					}
				]
			]
		);

		$hooks->trigger(new Event('test.a'));
		$hooks->trigger(new Event('test.b'));

		$this->assertSame(2, $count);
	}

	public function testTriggerWithBoundApp()
	{
		$self  = $this;
		$hooks = new Hooks(
			bind: $app = $this->app,
			hooks: [
				'test' => [
					function () use ($self, $app) {
						$self->assertSame($this, $app);
					}
				]
			]
		);

		$hooks->trigger(new Event('test'));
	}

	public function testTriggerWithMultipleHandlers()
	{
		$count = 0;
		$hooks = new Hooks(
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

		$hooks->trigger(new Event('test'));

		$this->assertSame(2, $count);
	}
}
