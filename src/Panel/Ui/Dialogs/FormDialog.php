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
class FormDialog extends Dialog
{
	public function __construct(
		string|null $component = 'k-form-dialog',
		string|array|false|null $cancelButton = null,
		public array $fields = [],
		string|null $size = 'medium',
		string|array|false|null $submitButton = null,
		public string|null $text = null,
		public array $value = []
	) {
		parent::__construct(
			component:    $component,
			cancelButton: $cancelButton,
			size:         $size,
			submitButton: $submitButton
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'fields' => $this->fields,
			'text'   => $this->text,
			'value'  => $this->value
		];
	}
}
