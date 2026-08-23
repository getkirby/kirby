<?php

namespace Kirby\Reflection;

use Kirby\Reflection\Attributes\Derived;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Describes a class' properties
 * through the class' constructor signature
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
		protected object|string $objectOrClass
	) {
		$this->class       = new ReflectionClass($objectOrClass);
		$this->constructor = new Constructor($objectOrClass);
	}

	/**
	 * Returns the value a user gets when the prop is not set
	 */
	protected function default(
		ReflectionParameter $parameter,
		ReflectionProperty|null $property = null
	): mixed {
		// `#[Derived]` may document a default the property cannot hold
		if ($this->derived($property)?->default !== null) {
			return $this->derived($property)->default;
		}

		if ($property?->hasDefaultValue() === true) {
			return $property->getDefaultValue();
		}

		return match ($parameter->isDefaultValueAvailable()) {
			true    => $parameter->getDefaultValue(),
			default => null
		};
	}

	/**
	 * Returns the `#[Derived]` attribute of a property, if it has one
	 */
	protected function derived(
		ReflectionProperty|null $property = null
	): Derived|null {
		$attribute = $property?->getAttributes(Derived::class)[0] ?? null;

		return $attribute?->newInstance();
	}

	/**
	 * Returns the docblock text of the property
	 * that belongs to a constructor parameter
	 */
	protected function description(ReflectionProperty|null $property = null): string|null
	{
		$comment = $property?->getDocComment();

		if ($comment === null || $comment === false) {
			return match ($property) {
				null    => null,
				default => $this->description($this->inherited($property))
			};
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
	 * Returns the same property as declared by the closest parent class
	 */
	protected function inherited(ReflectionProperty $property): ReflectionProperty|null
	{
		$name   = $property->getName();
		$parent = $property->getDeclaringClass()->getParentClass();

		if ($parent === false || $parent->hasProperty($name) === false) {
			return null;
		}

		return $parent->getProperty($name);
	}

	/**
	 * Returns the property that belongs to a constructor parameter
	 */
	protected function property(ReflectionParameter $parameter): ReflectionProperty|null
	{
		$name = $parameter->getName();

		return match ($this->class->hasProperty($name)) {
			true    => $this->class->getProperty($name),
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
			$property = $this->property($parameter);

			$props[$name] = [
				'name'        => $name,
				'type'        => (string)$parameter->getType(),
				'default'     => $this->default($parameter, $property),
				'derived'     => $this->derived($property) !== null,
				'description' => $this->description($property)
			];
		}

		ksort($props);

		return $props;
	}
}
