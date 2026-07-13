<?php

namespace Kirby\Form\Field;

use Kirby\Blueprint\Section;

/**
 * Section Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SectionField extends BaseField
{
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

	public function props(): array
	{
		return [
			...parent::props(),
			'sectionType' => $this->section,
		];
	}

	public function section(): Section
	{
		return new Section($this->section, [
			'model' => $this->model(),
			'name'  => $this->name(),
			...$this->props
		]);
	}
}
