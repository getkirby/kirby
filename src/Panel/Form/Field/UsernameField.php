<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TextField;

class UsernameField extends TextField
{
	public function icon(): string
	{
		return $this->icon ?? 'user';
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('name');
		}

		return parent::label();
	}

	public function name(): string
	{
		return $this->name ?? 'username';
	}

	public function type(): string
	{
		return 'text';
	}
}
