<?php

namespace Kirby\Form\FieldAbstract;

use Kirby\Cms\HasSiblings;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Fields;
use Kirby\Form\Mixin;
use Kirby\Toolkit\I18n;

/**
 * Abstract field class to be used as minimal
 * foundation for fields.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
 */
abstract class BaseField
{
	use HasSiblings;
	use Mixin\Api;
	use Mixin\Model;
	use Mixin\When;
	use Mixin\Width;

	/**
	 * A unique name for the field. The type is used as fallback.
	 */
	protected string|null $name;

	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	protected bool $disabled;

	/**
	 * Sibling fields in the same form
	 */
	protected Fields $siblings;

	public function __construct(
		bool $disabled = false,
		ModelWithContent|null $model = null,
		string|null $name = null,
		Fields|null $siblings = null,
		array|null $when = null,
		string|null $width = null
	) {
		$this->setDisabled($disabled);
		$this->setModel($model);
		$this->setSiblings($siblings);
		$this->setName($name);
		$this->setWhen($when);
		$this->setWidth($width);
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		return [];
	}

	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	public function disabled(): bool
	{
		return $this->disabled;
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		return [];
	}

	public static function factory(array $attrs = []): static
	{
		unset($attrs['type']);
		return new static(...$attrs);
	}

	abstract public function hasValue(): bool;

	protected function i18n(string|array|null $param = null): string|null
	{
		return empty($param) === false ? I18n::translate($param, $param) : null;
	}

	public function id(): string
	{
		return $this->name();
	}

	public function isDisabled(): bool
	{
		return $this->disabled;
	}

	abstract public function isHidden(): bool;

	public function isStorable(Language $language): bool
	{
		return $this->hasValue();
	}

	/**
	 * Returns the field name
	 */
	public function name(): string
	{
		return $this->name ?? $this->type();
	}

	/**
	 * Define the props that will be sent to
	 * the Vue component
	 */
	public function props(): array
	{
		return [
			'disabled' => $this->isDisabled(),
			'hidden'   => $this->isHidden(),
			'name'     => $this->name(),
			'saveable' => $this->hasValue(),
			'type'     => $this->type(),
			'when'     => $this->when(),
			'width'    => $this->width(),
		];
	}

	protected function setDisabled(bool $disabled = false): void
	{
		$this->disabled = $disabled;
	}

	protected function setName(string|null $name = null): void
	{
		$this->name = strtolower($name ?? $this->type());
	}

	protected function setSiblings(Fields|null $siblings = null): void
	{
		$this->siblings = $siblings ?? new Fields([$this]);
	}

	/**
	 * Returns all sibling fields for the HasSiblings trait
	 */
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
