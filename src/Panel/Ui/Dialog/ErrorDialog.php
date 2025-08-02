<?php

namespace Kirby\Panel\Ui\Dialog;

use Kirby\Panel\Ui\Dialog;

/**
 * Dialog to display an error message
 * and related details
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class ErrorDialog extends Dialog
{
	public function __construct(
		string|null $component = 'k-error-dialog',
		public array|null $details = null,
		public string|null $message = null,
		string|null $size = 'medium',
		...$attrs
	) {
		parent::__construct(...[
			...$attrs,
			'component'    => $component,
			'cancelButton' => false,
			'size'         => $size,
			'submitButton' => false
		]);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'details' => $this->details,
			'message' => $this->message
		];
	}
}
