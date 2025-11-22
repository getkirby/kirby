<?php

namespace Kirby\Reflection;

use Kirby\Form\FieldClass;
use ReflectionClass;

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

	/**
	 * Returns field properties based on the constructor signature.
	 */
	public function props(): array
	{
		$props  = [];
		$ignore = ['model', 'siblings'];

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
				'type'        => (string)$parameter->getType(),
				'default'     => $parameter->getDefaultValue(),
				'description' => $comment->description()
			];
		}

		ksort($props);

		return $props;
	}
}
