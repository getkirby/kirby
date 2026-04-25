<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\SelectField;

/**
 * Panel field override for selecting a page template
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class TemplateField extends SelectField
{
	public function __construct(
		protected array $blueprints = [],
		...$props
	) {
		parent::__construct(...$props);
	}

	public function isDisabled(): bool
	{
		return $this->disabled() === true || count($this->blueprints) <= 1;
	}

	public function icon(): string
	{
		return $this->icon ?? 'template';
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('template');
		}

		return parent::label();
	}

	public function name(): string
	{
		return $this->name ?? 'template';
	}

	public function options(): array
	{
		$options = [];

		foreach ($this->blueprints as $blueprint) {
			$options[] = [
				'text'  => $blueprint['title'] ?? $blueprint['text']  ?? null,
				'value' => $blueprint['name']  ?? $blueprint['value'] ?? null,
			];
		}

		return $options;
	}

	public function type(): string
	{
		return 'select';
	}
}
