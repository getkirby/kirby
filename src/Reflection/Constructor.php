<?php

namespace Kirby\Reflection;

use ReflectionMethod;
use ReflectionParameter;

/**
 * Specialized Reflection Method class to
 * inspect a class constructor
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.2.0
 */
class Constructor extends ReflectionMethod
{
	/**
	 * Cache of parameter names by class
	 *
	 * @var array<string, list<string>>
	 */
	protected static array $names = [];

	public function __construct(object|string $objectOrClass)
	{
		parent::__construct($objectOrClass, '__construct');
	}

	/**
	 * Group arguments into 'accepted' and 'ignored' arrays
	 *
	 * @param array<string, mixed> $arguments
	 * @return array{accepted: array<string, mixed>, ignored: array<string, mixed>}
	 */
	public function classifyArguments(array $arguments): array
	{
		$names    = $this->getParameterNames();
		$accepted = [];
		$ignored  = [];

		foreach ($arguments as $name => $value) {
			if (in_array($name, $names, true) === true) {
				$accepted[$name] = $value;
			} else {
				$ignored[$name] = $value;
			}
		}

		return [
			'accepted' => $accepted,
			'ignored'  => $ignored
		];
	}

	/**
	 * Returns all arguments that are defined as constructor parameters
	 *
	 * @param array<string, mixed> $arguments
	 * @return array<string, mixed>
	 */
	public function getAcceptedArguments(array $arguments): array
	{
		return $this->classifyArguments($arguments)['accepted'];
	}

	/**
	 * Get all parameters, including parameters from all parent constructors
	 * when a variadic parameter is used (...)
	 *
	 * @return array<int, ReflectionParameter>
	 */
	public function getAllParameters(): array
	{
		$parameters = [];

		foreach ($this->getParameters() as $parameter) {
			if ($parameter->isVariadic() === true) {
				// the variadic parameter stands in for all parameters of the
				// parent constructor. Own parameters win, because they can
				// refine the type of an inherited parameter.
				foreach ($this->getParentParameters() as $inherited) {
					$parameters[$inherited->name] ??= $inherited;
				}

				continue;
			}

			$parameters[$parameter->name] = $parameter;
		}

		return array_values($parameters);
	}

	/**
	 * Returns all arguments that are not defined as constructor parameters
	 *
	 * @param array<string, mixed> $arguments
	 * @return array<string, mixed>
	 */
	public function getIgnoredArguments(array $arguments): array
	{
		return $this->classifyArguments($arguments)['ignored'];
	}

	/**
	 * Returns an array of all parameter names in this constructor
	 *
	 * @return list<string>
	 */
	public function getParameterNames(): array
	{
		return static::$names[$this->class] ??= array_values(array_map(
			fn (ReflectionParameter $parameter) => $parameter->name,
			$this->getAllParameters()
		));
	}

	/**
	 * Get all parameters from the parent constructor. This will go recursively
	 * through all parent classes, if they use a variadic parameter (...)
	 *
	 * @return array<int, ReflectionParameter>
	 */
	public function getParentParameters(): array
	{
		$parent = $this->getDeclaringClass()->getParentClass();

		if ($parent === false) {
			return [];
		}

		return (new static($parent->getName()))->getAllParameters();
	}
}
