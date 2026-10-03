<?php

namespace Kirby\Cms;

use Closure;

/**
 * The `Events` class outsources the logic of
 * `App::apply()` and `App::trigger()` methods
 * and makes them easier and more predictable to test.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
class Events
{
	protected int $level = 0;
	protected array $processed = [];

	public function __construct(
		protected App $app
	) {
	}

	/**
	 * Runs the hook and applies the result to the argument
	 * specified by the $modify parameter. By default, the
	 * first argument is modified.
	 */
	public function apply(
		string $name,
		array $args = [],
		string|null $modify = null
	): mixed {
		// modify the first argument by default
		$modify ??= array_key_first($args);

		return $this->process(
			$name,
			$args,
			// update $modify value after each hook callback
			fn ($event, $result) => $event->updateArgument($modify, $result),
			// return the modified value
			fn ($event) => $event->argument($modify)
		);
	}

	/**
	 * Returns all matching hook handlers for the given event
	 */
	public function hooks(Event $event): array
	{
		// get all hooks for the event name
		$name   = $event->name();
		$hooks  = $this->app->extensions('hooks') ?? [];
		$result = $hooks[$name] ?? [];

		// get all hooks for the event name wildcards
		foreach ($event->nameWildcards() as $wildcard) {
			$result = [
				...$result,
				...$hooks[$wildcard] ?? []
			];
		}

		return $result;
	}

	/**
	 * Runs the hook
	 *
	 * @return ($return is null ? void : mixed)
	 */
	protected function process(
		string $name,
		array $args,
		Closure|null $afterEach = null,
		Closure|null $return = null
	) {
		// create the event object and get all hook callbacks for this event
		$event = new Event($name, $args);
		$hooks = $this->hooks($event);

		$this->level++;

		foreach ($hooks as $hook) {
			// skip hooks that have already been processed
			if (in_array($hook, $this->processed[$name] ?? []) === true) {
				continue;
			}

			// mark the hook as processed, to avoid endless loops
			$this->processed[$name][] = $hook;

			// bind the Kirby instance to the hook and run it
			$result = $event->call($this->app, $hook);

			// run the afterEach callback
			if ($afterEach !== null) {
				$afterEach($event, $result);
			}
		}

		$this->level--;

		// reset the protection after the last nesting level has been closed
		if ($this->level === 0) {
			$this->processed = [];
		}

		// run the return callback
		if ($return !== null) {
			return $return($event);
		}
	}

	/**
	 * Runs the hook without modifying the arguments
	 */
	public function trigger(
		string $name,
		array $args = []
	): void {
		$this->process($name, $args);
	}
}
