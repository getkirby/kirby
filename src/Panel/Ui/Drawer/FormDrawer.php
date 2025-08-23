<?php

namespace Kirby\Panel\Ui\Drawer;

use Kirby\Panel\Ui\Drawer;
use Override;

/**
 * Drawer that contains a set of fields
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FormDrawer extends Drawer
{
	public function __construct(
		string|null $component = 'k-form-drawer',
		string|null $class = null,
		public bool $disabled = false,
		public string|null $empty = null,
		public array $fields = [],
		string|null $icon = null,
		array|null $options = null,
		string|null $style = null,
		string|null $title = null,
		public array $value = [],
		...$attrs
	) {
		parent::__construct(...[
			...$attrs,
			'component' => $component,
			'class'     => $class,
			'icon'      => $icon,
			'options'   => $options,
			'style'     => $style,
			'title'     => $title
		]);
	}

	#[Override]
	public function props(): array
	{
		return [
			...parent::props(),
			'disabled' => $this->disabled,
			'empty'    => $this->empty,
			'fields'   => $this->fields,
			'value'    => $this->value
		];
	}
}
