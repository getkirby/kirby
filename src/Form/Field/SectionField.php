<?php

namespace Kirby\Form\Field;

use Kirby\Blueprint\Section;

class SectionField extends BaseField
{
	protected string $section;
	protected array $props;

	public function __construct(
		string $section,
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

		$this->props   = $props;
		$this->section = $section;
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
			...$this->props
		]);
	}
}
