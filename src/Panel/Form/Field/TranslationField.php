<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\App;
use Kirby\Form\Field\SelectField;
use Kirby\Reflection\Attributes\Prop;

/**
 * Panel field override for selecting a language translation
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class TranslationField extends SelectField
{
	#[Prop]
	protected string|null $icon = 'translate';

	#[Prop(computed: true)]
	protected string|null $name = 'translation';

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('language');
		}

		return parent::label();
	}

	public function options(): array
	{
		$translations = [];

		foreach (App::instance()->translations() as $translation) {
			$translations[] = [
				'text'  => $translation->name(),
				'value' => $translation->code()
			];
		}

		return $translations;
	}

	public function type(): string
	{
		return 'select';
	}
}
