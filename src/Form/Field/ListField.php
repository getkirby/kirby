<?php

namespace Kirby\Form\Field;

/**
 * List Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class ListField extends WriterField
{
	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $icon = null,
		array|string|null $label = null,
		array|bool|null $marks = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		string|null $name = null,
		array|bool|null $nodes = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		bool|null $spellcheck = null,
		array|bool|null $toolbar = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			help: $help,
			icon: $icon,
			label: $label,
			marks: $marks,
			maxlength: $maxlength,
			minlength: $minlength,
			name: $name,
			nodes: $nodes,
			placeholder: $placeholder,
			required: $required,
			spellcheck: $spellcheck,
			toolbar: $toolbar,
			translate: $translate,
			when: $when,
			width: $width
		);
	}

	public function props(): array
	{
		$props = parent::props();

		unset(
			$props['autocomplete'],
			$props['counter'],
			$props['font'],
			$props['headings'],
			$props['inline'],
		);

		return $props;
	}
}
