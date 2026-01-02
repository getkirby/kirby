<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\App;
use Kirby\Form\Field\SelectField;

class TranslationField extends SelectField
{
	public function icon(): string
	{
		return $this->icon ?? 'translate';
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('language');
		}

		return parent::label();
	}

	public function name(): string
	{
		return $this->name ?? 'translation';
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
