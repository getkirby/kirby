<?php

namespace Kirby\Cms;

use Closure;
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
	 * The full event name
	 * (e.g. `page.create:after`)
	 */
	protected string $name;

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

	/**
	 * The event arguments
	 */
	protected array $arguments = [];

	/**
	 * Class constructor
	 *
	 * @param string $name Full event name
	 * @param array $arguments Associative array of named event arguments
	 */
	public function __construct(string $name, array $arguments = [])
	{
		// split the event name into `$type.$action:$state`
		// $action and $state are optional;
		// if there is more than one dot, $type will be greedy
		$regex = '/^(?<type>.+?)(?:\.(?<action>[^.]*?))?(?:\:(?<state>.*))?$/';
		preg_match($regex, $name, $matches, PREG_UNMATCHED_AS_NULL);

		$this->name      = $name;
		$this->type      = $matches['type'];
		$this->action    = $matches['action'] ?? null;
		$this->state     = $matches['state'] ?? null;
		$this->arguments = $arguments;
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
		$data          = $this->arguments();
		$data['event'] = $this;

		// magically call the hook with the arguments it requested
		$hook = new Controller($hook);
		return $hook->call($bind, $data);
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

		$this->arguments[$name] = $value;
	}
}
