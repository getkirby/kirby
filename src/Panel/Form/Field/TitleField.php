<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TextField;

class TitleField extends TextField
{
	public function icon(): string
	{
		return $this->icon ?? 'title';
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('title');
		}

		return parent::label();
	}

	public function name(): string
	{
		return $this->name ?? 'title';
	}

	public function type(): string
	{
		return 'text';
	}
}
