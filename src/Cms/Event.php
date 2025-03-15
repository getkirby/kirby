<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\ImmutableMemoryStorage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Controller;
use Stringable;

/**
 * The Event object is created whenever the `$kirby->trigger()`
 * or `$kirby->apply()` methods are called. It collects all
 * event information and handles calling the individual hooks.
 * @since 3.4.0
 *
 * @package   Kirby Cms
 * @author    Lukas Bestle <lukas@getkirby.com>,
 *            Ahmet Bora
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Event implements Stringable
{
	/**
	 * The event type
	 * (e.g. `page` in `page.create:after`)
	 */
	protected string $type;

	/**
	 * The event action
	 * (e.g. `create` in `page.create:after`)
	 */
	protected string|null $action;

	/**
	 * The event state
	 * (e.g. `after` in `page.create:after`)
	 */
	protected string|null $state;

	protected static array $processed = [];
	protected static int $level = 0;

	/**
	 * Class constructor
	 *
	 * @param string $name Full event name (e.g. `page.create:after`)
	 * @param array $arguments Associative array of named event arguments
	 */
	public function __construct(
		protected string $name,
		protected array $arguments = [],
		protected object|null $bind = null
	) {
		// split the event name into `$type.$action:$state`
		// $action and $state are optional;
		// if there is more than one dot, $type will be greedy
		$regex = '/^(?<type>.+?)(?:\.(?<action>[^.]*?))?(?:\:(?<state>.*))?$/';
		preg_match($regex, $name, $matches, PREG_UNMATCHED_AS_NULL);

		$this->type   = $matches['type'];
		$this->action = $matches['action'] ?? null;
		$this->state  = $matches['state'] ?? null;
	}

	/**
	 * Magic caller for event arguments
	 */
	public function __call(string $method, array $arguments = []): mixed
	{
		return $this->argument($method);
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Makes it possible to simply echo
	 * or stringify the entire object
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * Returns the action of the event (e.g. `create`)
	 * or `null` if the event name does not include an action
	 */
	public function action(): string|null
	{
		return $this->action;
	}

	/**
	 * Applies all hooks for the current event
	 * @since 5.0.0
	 *
	 * @param string|null $modify Argument name that is modified by the hooks
	 */
	public function apply(string|null $modify = null): mixed
	{
		$modify ??= array_key_first($this->arguments());

		return $this->process(
			each: fn (Closure $hook) => $this->updateArgument(
				name:  $modify,
				value: $this->call($this->bind, $hook)
			),
			final: fn () => $this->argument($modify)
		);
	}

	/**
	 * Returns a specific event argument
	 */
	public function argument(string $name): mixed
	{
		return $this->arguments[$name] ?? null;
	}

	/**
	 * Returns the arguments of the event
	 */
	public function arguments(): array
	{
		return $this->arguments;
	}

	/**
	 * Calls a hook with the event data and returns
	 * the hook's return value
	 *
	 * @param object|null $bind Optional object to bind to the hook function
	 */
	public function call(object|null $bind, Closure $hook): mixed
	{
		// collect the list of possible hook arguments
		$data = [
			...$this->arguments(),
			'event' => $this
		];

		// magically call the hook with the arguments it requested
		return (new Controller($hook))->call($bind, $data);
	}

	/**
	 * Returns the hooks for the current event
	 * @since 5.0.0
	 */
	protected function hooks(): array
	{
		// load all hooks for the current event
		$hooks = $this->bind?->extension('hooks', $this->name) ?? [];

		// get the hooks for all wildcard event names
		foreach ($this->nameWildcards() as $wildcard) {
			$hooks = [
				...$hooks,
				...$this->bind?->extension('hooks', $wildcard) ?? []
			];
		}

		return $hooks;
	}

	/**
	 * Returns the full name of the event
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Returns the full list of possible wildcard
	 * event names based on the current event name
	 */
	public function nameWildcards(): array
	{
		// if the event is already a wildcard event,
		// no further variation is possible
		if (
			$this->type === '*' ||
			$this->action === '*' ||
			$this->state === '*'
		) {
			return [];
		}

		if ($this->action !== null && $this->state !== null) {
			// full $type.$action:$state event

			return [
				$this->type . '.*:' . $this->state,
				$this->type . '.' . $this->action . ':*',
				$this->type . '.*:*',
				'*.' . $this->action . ':' . $this->state,
				'*.' . $this->action . ':*',
				'*:' . $this->state,
				'*'
			];
		}

		if ($this->state !== null) {
			// event without action: $type:$state

			return [
				$this->type . ':*',
				'*:' . $this->state,
				'*'
			];
		}

		if ($this->action !== null) {
			// event without state: $type.$action

			return [
				$this->type . '.*',
				'*.' . $this->action,
				'*'
			];
		}

		// event with a simple name
		return ['*'];
	}

	/**
	 * Processes all hooks for the current event
	 * ensuring that the same hook is not called twice
	 * @since 5.0.0
	 *
	 * @param \Closure $each Will be called for each hook
	 * @param \Closure|null $final Will be called after all hooks have been processed
	 */
	protected function process(
		Closure $each,
		Closure|null $final = null
	): mixed {
		$final ??= function () {};
		static::$level++;

		foreach ($this->hooks() as $hook) {
			// check if the hook has already been processed
			if (in_array($hook, static::$processed[$this->name] ?? []) === true) {
				continue;
			}

			// mark the hook as processed, to avoid endless loops
			static::$processed[$this->name][] = $hook;

			// run the callback for each hook
			$each->call($this, $hook);
		}

		static::$level--;

		// reset the processed hooks after the last level has been processed
		if (static::$level === 0) {
			static::$processed = [];
		}

		// run the callback after all hooks have been processed
		return $final->call($this);
	}

	/**
	 * Resets the protection against endless loops
	 * by resetting the processed hooks and nesting level
	 * @since 5.0.0
	 * @codeCoverageIgnore
	 */
	public static function reset(): void
	{
		static::$processed = [];
		static::$level     = 0;
	}

	/**
	 * Returns the state of the event (e.g. `after`)
	 */
	public function state(): string|null
	{
		return $this->state;
	}

	/**
	 * Returns the event data as array
	 */
	public function toArray(): array
	{
		return [
			'name'      => $this->name,
			'arguments' => $this->arguments
		];
	}

	/**
	 * Returns the event name as string
	 */
	public function toString(): string
	{
		return $this->name;
	}

	/**
	 * Triggers all hooks for the current event
	 * @since 5.0.0
	 */
	public function trigger(): void
	{
		$this->process(function (Closure $hook) {
			$this->call($this->bind, $hook);
		});
	}

	/**
	 * Returns the type of the event (e.g. `page`)
	 */
	public function type(): string
	{
		return $this->type;
	}

	/**
	 * Updates a given argument with a new value
	 *
	 * @internal
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function updateArgument(string $name, $value): void
	{
		if (array_key_exists($name, $this->arguments) !== true) {
			throw new InvalidArgumentException(
				message: 'The argument ' . $name . ' does not exist'
			);
		}

		// no new value has been supplied by the apply hook
		if ($value === null) {

			// To support legacy model modification
			// in hooks without return values, we need to
			// check the state of the updated argument.
			// If the argument is an instance of ModelWithContent
			// and the storage is an instance of ImmutableMemoryStorage,
			// we can replace the argument with its clone to achieve
			// the same effect as if the hook returned the modified model.
			$state = $this->arguments[$name];

			if ($state instanceof ModelWithContent) {
				$storage = $state->storage();

				if (
					$storage instanceof ImmutableMemoryStorage &&
					$next = $storage->nextModel()
				) {
					$this->arguments[$name] = $next;
				}
			}

			// Otherwise, there's no need to update the argument
			// if no new value is provided
			return;
		}

		$this->arguments[$name] = $value;
	}
}
