<?php

namespace Kirby\Form\Field;

/**
 * Multiselect Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class MultiselectField extends TagsField
{
	public function __construct(
		string|null $accept = null,
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		string|null $icon = null,
		array|string|null $help = null,
		array|string|null $label = null,
		string|null $layout = null,
		int|null $max = null,
		int|null $min = null,
		string|null $name = null,
		array|string|null $options = null,
		bool|null $required = null,
		array|bool|null $search = null,
		string|null $separator = null,
		bool|null $sort = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			accept:    $accept,
			autofocus: $autofocus,
			default:   $default,
			disabled:  $disabled,
			help:      $help,
			icon:      $icon,
			label:     $label,
			layout:    $layout,
			name:      $name,
			max:       $max,
			min:       $min,
			options:   $options,
			required:  $required,
			search:    $search,
			separator: $separator,
			sort:      $sort,
			translate: $translate,
			when:      $when,
			width:     $width
		);
	}

	public function accept(): string
	{
		return match($this->accept) {
			'all'   => 'all',
			default => 'options'
		};
	}

	public function icon(): string
	{
		return $this->icon ?? 'checklist';
	}
}
