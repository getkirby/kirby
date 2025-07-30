<?php

namespace Kirby\Form;

use Kirby\Cms\HasSiblings;
use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\HasI18n;

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
abstract class FieldClass
{
	use HasI18n;
	use HasSiblings;
	use Mixin\After;
	use Mixin\Api;
	use Mixin\Autofocus;
	use Mixin\Before;
	use Mixin\Disabled;
	use Mixin\Help;
	use Mixin\Hidden;
	use Mixin\Icon;
	use Mixin\Label;
	use Mixin\Model;
	use Mixin\Placeholder;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\Value;
	use Mixin\When;
	use Mixin\Width;

	protected string|null $name;
	protected array $params = [];
	protected Fields $siblings;

	public function __construct(
		array|string|null $after = null,
		bool $autofocus = false,
		array|string|null $before = null,
		mixed $default = null,
		bool $disabled = false,
		array|string|null $help = null,
		bool $hidden = false,
		string|null $icon = null,
		array|string|null $label = null,
		ModelWithContent|null $model = null,
		string|null $name = null,
		array|string|null $placeholder = null,
		bool $required = false,
		Fields|null $siblings = null,
		bool $translate = true,
		array|null $when = null,
		string|null $width = null,
		mixed $value = null,
		// additional parameters can be passed to the field
		...$params
	) {
		$this->setAfter($after);
		$this->setAutofocus($autofocus);
		$this->setBefore($before);
		$this->setDefault($default);
		$this->setDisabled($disabled);
		$this->setHelp($help);
		$this->setHidden($hidden);
		$this->setIcon($icon);
		$this->setLabel($label);
		$this->setModel($model);
		$this->setName($name);
		$this->setPlaceholder($placeholder);
		$this->setRequired($required);
		$this->setSiblings($siblings);
		$this->setTranslate($translate);
		$this->setWhen($when);
		$this->setWidth($width);
		$this->fill($value);

		// set additional parameters
		$this->params = $params;
	}

	public function __call(string $param, array $args): mixed
	{
		if (isset($this->$param) === true) {
			return $this->$param;
		}

		return $this->params[$param] ?? null;
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		return [];
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		return [];
	}

	public function id(): string
	{
		return $this->name();
	}

	/**
	 * Returns the field name
	 */
	public function name(): string
	{
		return $this->name ?? $this->type();
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
			'after'       => $this->after(),
			'autofocus'   => $this->autofocus(),
			'before'      => $this->before(),
			'default'     => $this->default(),
			'disabled'    => $this->isDisabled(),
			'help'        => $this->help(),
			'hidden'      => $this->hidden(),
			'icon'        => $this->icon(),
			'label'       => $this->label(),
			'name'        => $this->name(),
			'placeholder' => $this->placeholder(),
			'required'    => $this->isRequired(),
			'saveable'    => $this->hasValue(),
			'translate'   => $this->translate(),
			'type'        => $this->type(),
			'when'        => $this->when(),
			'width'       => $this->width(),
		];
	}

	protected function setName(string|null $name = null): void
	{
		$this->name = strtolower($name ?? $this->type());
	}

	protected function setSiblings(Fields|null $siblings = null): void
	{
		$this->siblings = $siblings ?? new Fields([$this]);
	}

	protected function siblingsCollection(): Fields
	{
		return $this->siblings;
	}

	/**
	 * Parses a string template in the given value
	 */
	protected function stringTemplate(string|null $string = null): string|null
	{
		if ($string !== null) {
			return $this->model->toString($string);
		}

		return null;
	}

	/**
	 * Converts the field to a plain array
	 */
	public function toArray(): array
	{
		$props = $this->props();

		ksort($props);

		return array_filter($props, fn ($item) => $item !== null);
	}

	/**
	 * Returns the field type
	 */
	public function type(): string
	{
		return lcfirst(basename(str_replace(['\\', 'Field'], ['/', ''], static::class)));
	}
}
