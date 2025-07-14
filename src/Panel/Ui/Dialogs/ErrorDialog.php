<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Panel\Ui\Dialog;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class ErrorDialog extends Dialog
{
	public function __construct(
		string|null $component = 'k-error-dialog',
		public array|null $details = null,
		public string|null $message = null,
		string|null $size = 'medium'
	) {
		parent::__construct(
			component:    $component,
			cancelButton: false,
			size:         $size,
			submitButton: false
		);
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
