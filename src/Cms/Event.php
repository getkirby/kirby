<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Controller;

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
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Event
{
    /**
     * The full event name
     * (e.g. `page.create:after`)
     *
     * @var string
     */
    protected $name;

    /**
     * The event type
     * (e.g. `page` in `page.create:after`)
     *
     * @var string
     */
    protected $type;

    /**
     * The event action
     * (e.g. `create` in `page.create:after`)
     *
     * @var string|null
     */
    protected $action;

    /**
     * The event state
     * (e.g. `after` in `page.create:after`)
     *
     * @var string|null
     */
    protected $state;

    /**
     * The event arguments
     *
     * @var array
     */
    protected $arguments = [];

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
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        return $this->argument($method);
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /**
     * Makes it possible to simply echo
     * or stringify the entire object
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Returns the action of the event (e.g. `create`)
     * or `null` if the event name does not include an action
     *
     * @return string|null
     */
    public function action(): ?string
    {
        return $this->action;
    }

    /**
     * Returns a specific event argument
     *
     * @param string $name
     * @return mixed
     */
    public function argument(string $name)
    {
        if (isset($this->arguments[$name]) === true) {
            return $this->arguments[$name];
        }

        return null;
    }

    /**
     * Returns the arguments of the event
     *
     * @return array
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
     * @param \Closure $hook
     * @return mixed
     */
    public function call(?object $bind, Closure $hook)
    {
        // collect the list of possible hook arguments
        $data = $this->arguments();
        $data['event'] = $this;

        // magically call the hook with the arguments it requested
        $hook = new Controller($hook);
        return $hook->call($bind, $data);
    }

    /**
     * Returns the full name of the event
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the full list of possible wildcard
     * event names based on the current event name
     *
     * @return array
     */
    public function nameWildcards(): array
    {
        // if the event is already a wildcard event, no further variation is possible
        if ($this->type === '*' || $this->action === '*' || $this->state === '*') {
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
        } elseif ($this->state !== null) {
            // event without action: $type:$state

            return [
                $this->type . ':*',
                '*:' . $this->state,
                '*'
            ];
        } elseif ($this->action !== null) {
            // event without state: $type.$action

            return [
                $this->type . '.*',
                '*.' . $this->action,
                '*'
            ];
        } else {
            // event with a simple name

            return ['*'];
        }
    }

    /**
     * Returns the state of the event (e.g. `after`)
     *
     * @return string|null
     */
    public function state(): ?string
    {
        return $this->state;
    }

    /**
     * Returns the event data as array
     *
     * @return array
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
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->name;
    }

    /**
     * Returns the type of the event (e.g. `page`)
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Updates a given argument with a new value
     *
     * @internal
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function updateArgument(string $name, $value): void
    {
        if (array_key_exists($name, $this->arguments) !== true) {
            throw new InvalidArgumentException('The argument ' . $name . ' does not exist');
        }

        $this->arguments[$name] = $value;
    }
}
