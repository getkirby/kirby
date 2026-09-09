<?php

namespace Kirby\Reflection;

use Kirby\Reflection\Attributes\Computed;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use Throwable;

/**
 * Describes an object through its constructor signature: every
 * parameter becomes a documented property with type, default value
 * and description. Variadic constructors are resolved against the
 * parent constructors by `Constructor::getAllParameters()`.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Props
{
	protected ReflectionClass $class;
	protected Constructor $constructor;

	public function __construct(
		protected object $object
	) {
		$this->class       = new ReflectionClass($object);
		$this->constructor = new Constructor($object);
	}

	/**
	 * Returns the effective default value for the given parameter.
	 * The getter is preferred over the parameter default, because it
	 * resolves the value a user actually gets (e.g. `width()` = `1/1`,
	 * while the parameter default is `null`).
	 */
	protected function default(
		ReflectionParameter $parameter,
		ReflectionProperty|null $property = null
	): mixed {
		if ($attribute = $property?->getAttributes(Computed::class)[0] ?? null) {
			return $attribute->newInstance()->default
				?? $this->parameterDefault($parameter);
		}

		$name = $parameter->getName();

		if (method_exists($this->object, $name) === false) {
			return $this->parameterDefault($parameter);
		}

		try {
			$value = $this->object->$name();
		} catch (Throwable) {
			// some getters need a model that a bare instance doesn't have
			return $this->parameterDefault($parameter);
		}

		// getters that build a collection or a wrapper object
		// return a view of the prop, never its default
		if (is_object($value) === true) {
			return $this->parameterDefault($parameter);
		}

		return $value;
	}

	/**
	 * Returns the cleaned docblock text of the property
	 * that belongs to a constructor parameter
	 */
	protected function description(ReflectionProperty|null $property = null): string|null
	{
		$comment = $property?->getDocComment();

		if ($comment === null || $comment === false) {
			return null;
		}

		$lines = preg_split('/\R/', $comment) ?: [];
		$lines = array_map(
			static fn (string $line): string => trim(ltrim(trim($line), '*/ ')),
			$lines
		);
		$lines = array_filter(
			$lines,
			static fn (string $line): bool =>
				$line !== '' && str_starts_with($line, '@') === false
		);

		return implode(' ', $lines) ?: null;
	}

	/**
	 * Returns the declared default of a parameter, if it has one
	 */
	protected function parameterDefault(ReflectionParameter $parameter): mixed
	{
		return match ($parameter->isDefaultValueAvailable()) {
			true    => $parameter->getDefaultValue(),
			default => null
		};
	}

	/**
	 * Returns all documented properties, sorted by name
	 */
	public function toArray(): array
	{
		$props = [];

		foreach ($this->constructor->getAllParameters() as $parameter) {
			$name     = $parameter->getName();
			$property = match ($this->class->hasProperty($name)) {
				true    => $this->class->getProperty($name),
				default => null
			};

			$props[$name] = [
				'name'        => $name,
				'type'        => (string)$parameter->getType(),
				'default'     => $this->default($parameter, $property),
				'description' => $this->description($property)
			];
		}

		ksort($props);

		return $props;
	}
}
