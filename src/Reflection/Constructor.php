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
	 * Get all parameters, including parameters from all parent constructors
	 * when a variadic parameter is used (...)
	 */
	public function getAllParameters(): array
	{
		$parameters = [];

		foreach ($this->getParameters() as $parameter) {
			if ($parameter->isVariadic() === true) {
				foreach ($this->getParentParameters() as $parameter) {
					$parameters[] = $parameter;
				}

				continue;
			}

			$parameters[] = $parameter;
		}

		return $parameters;
	}

	/**
	 * Returns all arguments that are not defined as constructor parameters
	 */
	public function getIgnoredArguments(array $arguments): array
	{
		return $this->classifyArguments($arguments)['ignored'];
	}

	/**
	 * Returns an array of all parameter names in this constructor
	 */
	public function getParameterNames(): array
	{
		return array_values(array_map(fn (ReflectionParameter $param) => $param->name, $this->getAllParameters()));
	}

	/**
	 * Get all parameters from the parent constructor. This will go recursively
	 * through all parent classes, if they use a variadic parameter (...)
	 */
	public function getParentParameters(): array
	{
		if ($parentClass = $this->getDeclaringClass()->getParentClass()) {
			$parentConstructor = new static($parentClass->getName());
			return $parentConstructor->getAllParameters();
		}

		return [];
	}
}
