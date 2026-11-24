<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Form\Field\TextField;
use Kirby\Reflection\Attributes\Prop;

/**
 * Panel field override for the page or file title with panel defaults
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class TitleField extends TextField
{
	#[Prop]
	protected string|null $icon = 'title';

	#[Prop(computed: true)]
	protected string|null $name = 'title';

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('title');
		}

		return parent::label();
	}

	public function type(): string
	{
		return 'text';
	}
}
