<?php

namespace Kirby\Panel\Ui\Dialogs;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class RemoveDialog extends TextDialog
{
	public function __construct(
		string|null $component = 'k-remove-dialog',
		string|array|bool|null $cancelButton = null,
		string|null $size = 'medium',
		string|array|bool|null $submitButton = null,
		public string|null $text = null
	) {
		parent::__construct(
			component:    $component,
			cancelButton: $cancelButton,
			size:         $size,
			submitButton: $submitButton ?? [
				'icon'  => 'trash',
				'theme' => 'negative'
			],
			text: $text
		);
	}
}
