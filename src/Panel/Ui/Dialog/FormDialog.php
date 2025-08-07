<?php

namespace Kirby\Panel\Ui\Dialog;

use Kirby\Panel\Ui\Dialog;

/**
 * Dialog that contains a set of fields
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FormDialog extends TextDialog
{
	public function __construct(
		string|null $component = 'k-form-dialog',
		string|array|bool|null $cancelButton = null,
		public array $fields = [],
		string|null $size = 'medium',
		string|array|bool|null $submitButton = null,
		string|null $text = null,
		public array $value = [],
		...$attrs
	) {
		parent::__construct(...[
			...$attrs,
			'component'    => $component,
			'cancelButton' => $cancelButton,
			'size'         => $size,
			'submitButton' => $submitButton,
			'text'         => $text
		]);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'fields' => $this->fields,
			'value'  => $this->value
		];
	}
}
