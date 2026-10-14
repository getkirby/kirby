<?php

namespace Kirby\Reflection;

use Closure;
use Kirby\Exception\LogicException;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\A;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Describes, sets and exports all properties of
 * a class that are marked with the `#[Prop]` attribute
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Props
{
	/**
	 * Cache for the prop definitions per class
	 */
	protected static array $cache = [];

	/**
	 * Cache for the closures that read the exported values per class
	 */
	protected static array $exporters = [];

	/**
	 * Cache for the exported props per class, sorted by
	 * their export name, with the getter or property to read
	 */
	protected static array $exports = [];

	/**
	 * The prop definitions for this instance
	 */
	protected array|null $props = null;

	public function __construct(
		protected object|string $objectOrClass
	) {
	}

	/**
	 * Returns all props of the class with their attribute,
	 * property, getter and setter method, sorted by name
	 *
	 * @throws LogicException if a getter or setter is invalid or two props are exported under the same name
	 */
	public function all(): array
	{
		if ($this->props !== null) {
			return $this->props;
		}

		$class = $this->objectOrClass;

		if (is_object($class) === true) {
			$class = $class::class;
		}

		if (isset(static::$cache[$class]) === true) {
			return $this->props = static::$cache[$class];
		}

		$reflection = new ReflectionClass($class);
		$props      = [];

		foreach ($reflection->getProperties() as $property) {
			$attribute = $this->attribute($property);

			if ($attribute === null) {
				continue;
			}

			$props[$property->getName()] = [
				'attribute' => $attribute,
				'getter'    => $this->getter($reflection, $property, $attribute),
				'property'  => $property,
				'setter'    => $this->setter($reflection, $property, $attribute)
			];
		}

		ksort($props);

		$this->checkExportNames($class, $props);

		return $this->props = static::$cache[$class] = $props;
	}

	/**
	 * Returns the `#[Prop]` attribute of the property,
	 * falling back to the one of a redeclared parent property
	 */
	protected function attribute(ReflectionProperty $property): Prop|null
	{
		$attribute = $property->getAttributes(Prop::class)[0] ?? null;

		if ($attribute !== null) {
			return $attribute->newInstance();
		}

		if ($inherited = $this->inherited($property)) {
			return $this->attribute($inherited);
		}

		return null;
	}

	/**
	 * Makes sure that no two props are exported under the same name
	 *
	 * @throws LogicException
	 */
	protected function checkExportNames(string $class, array $props): void
	{
		$names = [];

		foreach ($props as $name => $prop) {
			$key = $prop['attribute']->export($name);

			if ($key === null) {
				continue;
			}

			if (isset($names[$key]) === true) {
				throw new LogicException(
					message: 'The props "' . $names[$key] . '" and "' . $name . '" of ' . $class . ' are both exported as "' . $key . '"'
				);
			}

			$names[$key] = $name;
		}
	}

	/**
	 * Returns the value a user gets when the prop is not set
	 */
	protected function default(
		Prop $attribute,
		ReflectionProperty $property
	): mixed {
		// `#[Prop]` may document a default the property cannot hold
		if ($attribute->default !== null) {
			return $attribute->default;
		}

		if ($property->hasDefaultValue() === true) {
			return $property->getDefaultValue();
		}

		return null;
	}

	/**
	 * Returns the docblock text of the property
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
	 * Returns a closure bound to the class scope that
	 * reads exported values, incl. protected ones
	 */
	protected static function exporter(string $class): Closure
	{
		if (isset(static::$exporters[$class]) === true) {
			return static::$exporters[$class];
		}

		$exporter = Closure::bind(
			static function (object $object, array $exports): array {
				$values = [];

				foreach ($exports as $key => [$getter, $property]) {
					$values[$key] = match ($getter) {
						null    => $object->$property ?? null,
						default => $object->$getter()
					};
				}

				return $values;
			},
			null,
			$class
		);

		return static::$exporters[$class] = $exporter ?? throw new LogicException(
			message: 'The props of ' . $class . ' cannot be exported'
		);
	}

	/**
	 * Returns the getter named in the attribute or
	 * the public method named like the property
	 *
	 * @throws LogicException if the named getter is invalid
	 */
	protected function getter(
		ReflectionClass $class,
		ReflectionProperty $property,
		Prop $attribute
	): ReflectionMethod|null {
		if ($attribute->getter !== null) {
			$method = match ($class->hasMethod($attribute->getter)) {
				true    => $class->getMethod($attribute->getter),
				default => null
			};

			if (
				$method === null ||
				$method->isStatic() === true ||
				$method->getNumberOfRequiredParameters() > 0
			) {
				throw new LogicException(
					message: 'The getter "' . $attribute->getter . '" for the prop "' . $property->getName() . '" must be a non-static method of ' . $class->getName() . ' without required arguments'
				);
			}

			return $method;
		}

		$name = $property->getName();

		if ($class->hasMethod($name) === false) {
			return null;
		}

		$method = $class->getMethod($name);

		if (
			$method->isPublic() === false ||
			$method->isStatic() === true ||
			$method->getNumberOfRequiredParameters() > 0
		) {
			return null;
		}

		return $method;
	}

	/**
	 * Checks if the class has a prop with the given name
	 */
	public function has(string $name): bool
	{
		return isset($this->all()[$name]);
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
	 * Returns the object
	 *
	 * @throws LogicException if only a class name was given
	 */
	protected function object(): object
	{
		if (is_object($this->objectOrClass) === false) {
			throw new LogicException(
				message: 'Props can only be set or exported for an object'
			);
		}

		return $this->objectOrClass;
	}

	/**
	 * Sets the value of a prop via its setter or directly,
	 * merging associative arrays into the current value
	 *
	 * @throws LogicException if the prop does not exist
	 * @throws \TypeError if the value has the wrong type
	 */
	public function set(string $name, mixed $value): void
	{
		$object = $this->object();
		$prop   = $this->all()[$name] ?? throw new LogicException(
			message: 'The prop "' . $name . '" does not exist'
		);

		if ($prop['setter'] !== null) {
			$prop['setter']->invoke($object, $value);
			return;
		}

		$property = $prop['property'];

		if (
			is_array($value) === true &&
			$property->isInitialized($object) === true &&
			is_array($current = $property->getValue($object)) === true
		) {
			$value = A::merge($current, $value, A::MERGE_REPLACE);
		}

		$property->setValue($object, $value);
	}

	/**
	 * Returns the setter method that is named in the attribute, if any.
	 * The setter may also be protected.
	 *
	 * @throws LogicException if the named setter is invalid
	 */
	protected function setter(
		ReflectionClass $class,
		ReflectionProperty $property,
		Prop $attribute
	): ReflectionMethod|null {
		if ($attribute->setter === null) {
			return null;
		}

		$method = match ($class->hasMethod($attribute->setter)) {
			true    => $class->getMethod($attribute->setter),
			default => null
		};

		if (
			$method === null ||
			$method->isStatic() === true ||
			$method->getNumberOfParameters() === 0 ||
			$method->getNumberOfRequiredParameters() > 1
		) {
			throw new LogicException(
				message: 'The setter "' . $attribute->setter . '" for the prop "' . $property->getName() . '" must be a non-static method of ' . $class->getName() . ' that accepts the value as its only required argument'
			);
		}

		return $method;
	}

	/**
	 * Returns the documentation for all props, sorted by name
	 */
	public function toArray(): array
	{
		$props = [];

		foreach ($this->all() as $name => $prop) {
			$props[$name] = [
				'name'        => $name,
				'type'        => $this->type($name),
				'computed'    => $prop['attribute']->computed,
				'default'     => $this->default($prop['attribute'], $prop['property']),
				'description' => $this->description($prop['property'])
			];
		}

		return $props;
	}

	/**
	 * Returns the type of value that the prop accepts.
	 * A setter may accept more types than the property itself.
	 */
	public function type(string $name): string|null
	{
		$prop = $this->all()[$name] ?? null;

		if ($prop === null) {
			return null;
		}

		$type = match ($prop['setter']) {
			null    => $prop['property']->getType(),
			default => $prop['setter']->getParameters()[0]->getType()
		};

		return $type === null ? 'mixed' : (string)$type;
	}

	/**
	 * Returns the values of all exported props,
	 * read via their getter if there is one
	 *
	 * @throws LogicException if no object is given
	 */
	public function values(): array
	{
		$object = $this->object();
		$class  = $object::class;

		if (isset(static::$exports[$class]) === false) {
			$exports = [];

			foreach ($this->all() as $name => $prop) {
				$key = $prop['attribute']->export($name);

				if ($key === null) {
					continue;
				}

				$exports[$key] = [$prop['getter']?->getName(), $name];
			}

			ksort($exports);

			static::$exports[$class] = $exports;
		}

		return static::exporter($class)($object, static::$exports[$class]);
	}
}
