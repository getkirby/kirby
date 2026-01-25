<?php

namespace Kirby\Reflection;

use Kirby\Form\FieldClass;
use Kirby\Reflection\Attributes\DefaultValue;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;

class Field
{
	protected Constructor $constructor;
	protected ReflectionClass $class;

	public function __construct(
		protected FieldClass|string $field
	) {
		$this->class       = new ReflectionClass($field);
		$this->constructor = new Constructor($field);
	}

	protected function parentClass(): ReflectionClass
	{
		return $this->class->getParentClass();
	}

	protected function parentProps(): array
	{
		return $this->parentReflection()->props();
	}

	protected function parentReflection(): static
	{
		return new static($this->parentClass()->getName());
	}

	protected function propDefaultValue(
		ReflectionParameter $parameter,
		ReflectionProperty|null $property = null
	): mixed {
		if ($property !== null && $attribute = ($property->getAttributes(DefaultValue::class)[0] ?? null)) {
			return $attribute->newInstance()->value();
		}

		$value = $this->field->{$parameter->getName()}();

		return match (true) {
			is_object($value) => $value::class,
			default           => $value
		};
	}

	protected function propType(ReflectionParameter $parameter): string
	{
		$type = $parameter->getType();

		if ($type instanceof ReflectionUnionType) {
			return $type;
		}

		$string = $type->getName();

		if ($type->allowsNull() === true) {
			$string .= '|null';
		}

		return $string;
	}

	/**
	 * Returns field properties based on the constructor signature.
	 */
	public function props(): array
	{
		$props  = [];
		$ignore = ['model', 'siblings'];
		$field  = $this->field;

		foreach ($this->constructor->getParameters() as $parameter) {
			$name = $parameter->getName();

			if (in_array($name, $ignore) === true) {
				continue;
			}

			// resolve ... by getting props from the parent class
			if ($parameter->isVariadic()) {
				$props = [
					...$this->parentProps(),
					...$props,
				];
				continue;
			}

			$property = $this->class->hasProperty($name) ? $this->class->getProperty($name) : null;
			$comment  = DocComment::from($property);

			$props[$name] = [
				'name'        => $name,
				'type'        => $this->propType($parameter),
				'default'     => $this->propDefaultValue($parameter, $property),
				'description' => $comment->description()
			];
		}

		ksort($props);

		return $props;
	}
}
