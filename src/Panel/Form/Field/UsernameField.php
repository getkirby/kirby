<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TextField;

/**
 * Panel field override for the user's display name
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
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
