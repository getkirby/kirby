<?php

namespace Kirby\Reflection;

use ReflectionMethod;
use ReflectionParameter;

/**
 * Specialized Reflection Method class to
 * inspect a class constructor
 *
 * @package   Kirby Reflection
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.2.0
 */
class Constructor extends ReflectionMethod
{
	public function __construct(object|string $objectOrClass)
	{
		parent::__construct($objectOrClass, '__construct');
	}

	/**
	 * Group arguments into 'accepted' and 'ignored' arrays
	 */
	public function classifyArguments(array $arguments): array
	{
		$parameterNames = $this->getParameterNames();

		$accepted = [];
		$ignored  = [];

		foreach ($arguments as $argumentName => $argumentValue) {
			if (in_array($argumentName, $parameterNames, true) === true) {
				$accepted[$argumentName] = $argumentValue;
			} else {
				$ignored[$argumentName] = $argumentValue;
			}
		}

		return [
			'accepted' => $accepted,
			'ignored'  => $ignored
		];
	}

	/**
	 * Returns all arguments that are defined as constructor parameters
	 */
	public function getAcceptedArguments(array $arguments): array
	{
		return $this->classifyArguments($arguments)['accepted'];
	}

	/**
	 * Returns all arguments that are not defined as constructor parameters
	 */
	public function getIgnoredArguments(array $arguments): array
	{
		return $this->classifyArguments($arguments)['ignored'];
	}

	/**
	 * Returns an array of all parameter names
	 */
	public function getParameterNames(): array
	{
		return array_values(array_map(fn (ReflectionParameter $param) => $param->name, $this->getParameters()));
	}
}
