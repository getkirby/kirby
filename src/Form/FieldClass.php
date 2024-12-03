<?php

namespace Kirby\Form;

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
 */
abstract class FieldClass
{
	use Mixin\Common;
	use Mixin\Decorators;
	use Mixin\Disabled;
	use Mixin\Endpoints;
	use Mixin\Model;
	use Mixin\Siblings;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\Value;
	use Mixin\When;

	public function __construct(
		protected array $params = []
	) {
		$this->setAfter($params['after'] ?? null);
		$this->setAutofocus($params['autofocus'] ?? false);
		$this->setBefore($params['before'] ?? null);
		$this->setDefault($params['default'] ?? null);
		$this->setDisabled($params['disabled'] ?? false);
		$this->setHelp($params['help'] ?? null);
		$this->setIcon($params['icon'] ?? null);
		$this->setLabel($params['label'] ?? null);
		$this->setModel($params['model'] ?? null);
		$this->setName($params['name'] ?? null);
		$this->setPlaceholder($params['placeholder'] ?? null);
		$this->setRequired($params['required'] ?? false);
		$this->setSiblings($params['siblings'] ?? null);
		$this->setTranslate($params['translate'] ?? true);
		$this->setWhen($params['when'] ?? null);
		$this->setWidth($params['width'] ?? null);
		$this->setValidate($params['validate'] ?? []);

		if (array_key_exists('value', $params) === true) {
			$this->fill($params['value']);
		}
	}

	/**
	 * Define the props that will be sent to
	 * the Vue component
	 */
	public function props(): array
	{
		return [
			'after'       => $this->after(),
			'autofocus'   => $this->autofocus(),
			'before'      => $this->before(),
			'default'     => $this->default(),
			'disabled'    => $this->isDisabled(),
			'help'        => $this->help(),
			'hidden'      => $this->isHidden(),
			'icon'        => $this->icon(),
			'label'       => $this->label(),
			'name'        => $this->name(),
			'placeholder' => $this->placeholder(),
			'required'    => $this->isRequired(),
			'saveable'    => $this->isSaveable(),
			'translate'   => $this->translate(),
			'type'        => $this->type(),
			'when'        => $this->when(),
			'width'       => $this->width(),
		];
	}

	/**
	 * Returns the field type
	 */
	public function type(): string
	{
		return lcfirst(basename(str_replace(['\\', 'Field'], ['/', ''], static::class)));
	}
}
