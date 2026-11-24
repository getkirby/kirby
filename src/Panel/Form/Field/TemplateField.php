<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\SelectField;
use Kirby\Reflection\Attributes\Prop;

/**
 * Panel field override for selecting a page template
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class TemplateField extends SelectField
{
	#[Prop]
	protected string|null $icon = 'template';

	#[Prop(computed: true)]
	protected string|null $name = 'template';

	public function __construct(
		protected array $blueprints = [],
		mixed ...$args
	) {
		parent::__construct(...$args);
	}

	public function isDisabled(): bool
	{
		return $this->disabled() === true || count($this->blueprints) <= 1;
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('template');
		}

		return parent::label();
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
