<?php

namespace Kirby\Cms;

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
		$event     = new Event($name, $args);
		$hooks    = $this->hooks($event);
		$modify ??= array_key_first($event->arguments());

		if ($hooks === []) {
			return $event->argument($modify);
		}

		$this->level++;

		foreach ($hooks as $hook) {
			if (in_array($hook, $this->processed[$name] ?? []) === true) {
				continue;
			}

			// mark the hook as processed, to avoid endless loops
			$this->processed[$name][] = $hook;

			// bind the Kirby instance to the hook
			$newValue = $event->call($this->bind, $hook);

			// update value if one was returned
			$event->updateArgument($modify, $newValue);
		}

		$this->level--;

		if ($this->level === 0) {
			$this->processed = [];
		}

		return $event->argument($modify);
	}

	/**
	 * Returns a list of all matching handlers
	 */
	public function hooks(Event $event): array
	{
		$name  = $event->name();
		$hooks = $this->hooks[$name] ??[];

		foreach ($event->nameWildcards() as $wildcard) {
			$hooks = [
				...$hooks,
				...$this->hooks[$wildcard] ?? []
			];
		}

		return $hooks;
	}

	/**
	 * Runs the hook without modifying the arguments
	 */
	public function trigger(
		string $name,
		array $args = []
	): void {
		$event = new Event($name, $args);
		$hooks = $this->hooks($event);

		if ($hooks === []) {
			return;
		}

		$this->level++;

		foreach ($hooks as $hook) {
			if (in_array($hook, $this->processed[$name] ?? []) === true) {
				continue;
			}

			// mark the hook as processed, to avoid endless loops
			$this->processed[$name][] = $hook;

			// bind the Kirby instance to the hook
			$event->call($this->bind, $hook);
		}

		$this->level--;

		if ($this->level === 0) {
			$this->processed = [];
		}
	}
}
