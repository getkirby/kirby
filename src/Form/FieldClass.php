<?php

namespace Kirby\Form;

use Kirby\Form\FieldAbstract\ValueField;

/**
 * Abstract field class to be used instead
 * of functional field components for more
 * control.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
 */
abstract class FieldClass extends ValueField
{
	use Mixin\After;
	use Mixin\Before;
	use Mixin\Icon;

	public function __construct(
		protected array $params = []
	) {
		parent::__construct(
			default: $params['default'] ?? null,
			disabled: $params['disabled'] ?? false,
			help: $params['help'] ?? null,
			label: $params['label'] ?? null,
			model: $params['model'] ?? null,
			name: $params['name'] ?? null,
			placeholder: $params['placeholder'] ?? null,
			required: $params['required'] ?? false,
			siblings: $params['siblings'] ?? null,
			translate: $params['translate'] ?? true,
			value: $params['value'] ?? null,
			when: $params['when'] ?? null,
			width: $params['width'] ?? null
		);

		$this->setAfter($params['after'] ?? null);
		$this->setAutofocus($params['autofocus'] ?? false);
		$this->setBefore($params['before'] ?? null);
		$this->setIcon($params['icon'] ?? null);
	}

	public function __call(string $param, array $args): mixed
	{
		if (isset($this->$param) === true) {
			return $this->$param;
		}

		return $this->params[$param] ?? null;
	}

	public static function factory(array $attrs = []): static
	{
		return new static($attrs);
	}

	/**
	 * Returns all original params for the field
	 */
	public function params(): array
	{
		return $this->params;
	}

	/**
	 * Define the props that will be sent to
	 * the Vue component
	 */
	public function props(): array
	{
		return [
			...parent::props(),
			'after'  => $this->after(),
			'before' => $this->before(),
			'icon'   => $this->icon(),
		];
	}
}
