<?php

namespace Kirby\Option;

use Kirby\Toolkit\I18n;

/**
 * Stand-in for a model that exists, but that the current user is not
 * allowed to list. It only keeps the value, so that the model is
 * neither disclosed nor dropped from a stored field value.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class ProtectedOption extends Option
{
	public function __construct(string|int|float|null $value)
	{
		parent::__construct(
			value: $value,
			text: '[' . I18n::translate('unknown', 'unknown') . ']',
			resolve: false
		);
	}
}
