<?php

namespace Kirby\Reflection;

use Kirby\Form\FieldClass;
use ReflectionClass;

class Field
{
	protected Constructor $constructor;
	protected ReflectionClass $class;

	public function __construct(
		protected FieldClass $field
	) {
		$this->class       = new ReflectionClass($field);
		$this->constructor = new Constructor($field);
	}

	/**
	 * Returns field properties based on the constructor signature.
	 */
	public function props(): array
	{
		$props  = [];
		$ignore = ['model', 'siblings', 'props'];

		foreach ($this->constructor->getParameters() as $parameter) {
			$name = $parameter->getName();

			if (in_array($name, $ignore) === true) {
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

		return $props;
	}
}
