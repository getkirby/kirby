<?php

namespace Kirby\Cms;

use Closure;

/**
 * The Hooks class outsources the logic of
 * `App::apply()` and `App::trigger()` methods
 * and makes them easier and more predictable to test.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class Events
{
	protected App|self $bind;
	protected array $hooks;
	protected array $processed = [];
	protected int $level = 0;

	public function __construct(
		array|null $hooks = null,
		App|null $bind = null
	) {
		$this->bind  = $bind ?? $this;
		$this->hooks = $hooks ?? $bind?->extensions('hooks') ?? [];
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
		$modify ??= array_key_first($args);

		return $this->process(
			$name,
			$args,
			fn ($event, $result) => $event->updateArgument($modify, $result),
			fn ($event) => $event->argument($modify)
		);
	}

	/**
	 * Returns all matching hook handlers for the given event
	 */
	public function hooks(Event $event): array
	{
		$name  = $event->name();
		$hooks = $this->hooks[$name] ?? [];

		foreach ($event->nameWildcards() as $wildcard) {
			$hooks = [
				...$hooks,
				...$this->hooks[$wildcard] ?? []
			];
		}

		return $hooks;
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
		$event = new Event($name, $args);
		$hooks = $this->hooks($event);

		$this->level++;

		foreach ($hooks as $hook) {
			if (in_array($hook, $this->processed[$name] ?? []) === true) {
				continue;
			}

			// mark the hook as processed, to avoid endless loops
			$this->processed[$name][] = $hook;

			// bind the Kirby instance to the hook
			$result = $event->call($this->bind, $hook);

			// run the afterEach callback
			if ($afterEach !== null) {
				$result = $afterEach($event, $result);
			}
		}

		$this->level--;

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
