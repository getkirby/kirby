<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TextField;
use Kirby\Reflection\Attributes\Prop;

/**
 * Panel field override for the user's display name
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UsernameField extends TextField
{
	#[Prop]
	protected string|null $icon = 'user';

	#[Prop(computed: true)]
	protected string|null $name = 'username';

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('name');
		}

		return parent::label();
	}

	public function type(): string
	{
		return 'text';
	}
}
