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
class Hooks
{
	protected array $applied = [];
	protected int $appliedLevel = 0;
	protected array $triggered = [];
	protected int $triggeredLevel = 0;
	protected App|self $bind;

	public function __construct(
		protected array $hooks,
		App|null $bind = null
	) {
		$this->bind = $bind ?? $this;
	}

	/**
	 * Runs the hook and applies the result to the argument
	 * specified by the $modify parameter. By default, the
	 * first argument is modified.
	 */
	public function apply(
		Event $event,
		string|null $modify = null,
	): mixed {
		$name     = $event->name();
		$hooks    = $this->hooks($event);
		$modify ??= array_key_first($event->arguments());

		if ($hooks === []) {
			return $event->argument($modify);
		}

		$this->appliedLevel++;

		foreach ($hooks as $hook) {
			if (in_array($hook, $this->applied[$name] ?? []) === true) {
				continue;
			}

			// mark the hook as applied, to avoid endless loops
			$this->applied[$name][] = $hook;

			// bind the Kirby instance to the hook
			$newValue = $event->call($this->bind, $hook);

			// update value if one was returned
			$event->updateArgument($modify, $newValue);
		}

		$this->appliedLevel--;

		if ($this->appliedLevel === 0) {
			$this->applied = [];
		}

		return $event->argument($modify);
	}

	/**
	 * Returns a list of all matching handlers
	 */
	public function hooks(Event $event): array
	{
		$name  = $event->name();
		$hooks = [];

		if (isset($this->hooks[$name]) === true) {
			$hooks = $this->hooks[$name];
		}

		foreach ($event->nameWildcards() as $wildcard) {
			if (isset($this->hooks[$wildcard]) === true) {
				$hooks = [
					...$hooks,
					...$this->hooks[$wildcard]
				];
			}
		}

		return $hooks;
	}

	/**
	 * Runs the hook without modifying the arguments
	 */
	public function trigger(
		Event $event
	): void {
		$name  = $event->name();
		$hooks = $this->hooks($event);

		if ($hooks === []) {
			return;
		}

		$this->triggeredLevel++;

		foreach ($hooks as $index => $hook) {
			if (in_array($hook, $this->triggered[$name] ?? []) === true) {
				continue;
			}

			// mark the hook as triggered, to avoid endless loops
			$this->triggered[$name][] = $hook;

			// bind the Kirby instance to the hook
			$event->call($this->bind, $hook);
		}

		$this->triggeredLevel--;

		if ($this->triggeredLevel === 0) {
			$this->triggered = [];
		}
	}
}
