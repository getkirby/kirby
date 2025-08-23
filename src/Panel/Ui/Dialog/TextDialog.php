<?php

namespace Kirby\Panel\Ui\Dialog;

use Kirby\Panel\Ui\Dialog;
use Override;

/**
 * Dialog that displays some text
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class TextDialog extends Dialog
{
	public function __construct(
		string|null $component = 'k-text-dialog',
		string|array|bool|null $cancelButton = null,
		string|null $size = 'medium',
		string|array|bool|null $submitButton = null,
		public string|null $text = null,
		...$attrs
	) {
		parent::__construct(...[
			...$attrs,
			'component'    => $component,
			'cancelButton' => $cancelButton,
			'size'         => $size,
			'submitButton' => $submitButton
		]);
	}

	#[Override]
	public function props(): array
	{
		return [
			...parent::props(),
			'text' => $this->text
		];
	}
}
