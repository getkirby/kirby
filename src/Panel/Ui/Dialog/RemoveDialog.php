<?php

namespace Kirby\Panel\Ui\Dialog;

use Kirby\Toolkit\HtmlString;

/**
 * Dialog that removes something
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class RemoveDialog extends TextDialog
{
	public function __construct(
		string|null $component = 'k-remove-dialog',
		string|array|bool|null $cancelButton = null,
		string|null $size = 'medium',
		string|array|bool|null $submitButton = null,
		string|HtmlString|null $text = null,
		...$attrs
	) {
		parent::__construct(...[
			...$attrs,
			'component'    => $component,
			'cancelButton' => $cancelButton,
			'size'         => $size,
			'submitButton' => $submitButton ?? [
				'icon'  => 'trash',
				'theme' => 'negative'
			],
			'text'         => $text
		]);
	}
}
