<?php

namespace Kirby\Form\Field;

use Kirby\Blueprint\Section;
use Kirby\Form\Fields;

/**
 * Section Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SectionField extends BaseField
{
	protected Section|null $instance = null;
	protected array $props;
	protected string $section;

	public function __construct(
		string|null $section = null,
		string|null $name = null,
		array|null $when = null,
		string|null $width = null,
		...$props
	) {
		parent::__construct(
			name: $name,
			when: $when,
			width: $width
		);

		$this->props = $props;

		// fall back to the field name as section type
		$this->section = $section ?? $this->name();
	}

	/**
	 * Returns the validation errors of the wrapped section.
	 * Inactive sections – hidden by a `when` condition –
	 * never have errors, just like regular fields.
	 */
	public function errors(): array
	{
		if ($this->isActive() === false) {
			return [];
		}

		return $this->sectionErrors()['message'] ?? [];
	}

	/**
	 * All props that are not part of the constructor signature
	 * are passed on to the section and must not be filtered out
	 * by the default factory
	 */
	public static function factory(
		array $props,
		Fields|null $siblings = null
	): static {
		$model = $props['model'] ?? null;

		unset($props['model'], $props['type'], $props['value']);

		$field = new static(...$props);
		$field->setSiblings($siblings);

		if ($model !== null) {
			$field->setModel($model);
		}

		return $field;
	}

	/**
	 * The section headline is used as label for error messages
	 */
	public function label(): string|null
	{
		return $this->sectionErrors()['label'] ?? null;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'sectionType' => $this->section,
		];
	}

	public function section(): Section
	{
		return $this->instance ??= new Section($this->section, [
			'field' => $this,
			'model' => $this->model(),
			'name'  => $this->name(),
			...$this->props
		]);
	}

	/**
	 * The raw error entry of the wrapped section,
	 * with its own `label` and `message` keys
	 */
	protected function sectionErrors(): array
	{
		return $this->section()->errors()[$this->name()] ?? [];
	}
}
