<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Hidden field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class HiddenField extends BaseField
{
	use Mixin\Value;

	protected mixed $value = null;

	public function __construct(
		mixed $default = null,
		string|null $name = null,
		bool|null $translate = null
	) {
		parent::__construct(
			name: $name
		);

		$this->default   = $default;
		$this->translate = $translate;
	}

	public function isHidden(): bool
	{
		return true;
	}

	public function props(): array
	{
		return [
			'hidden'   => $this->isHidden(),
			'name'     => $this->name(),
			'saveable' => $this->hasValue(),
			'type'     => $this->type(),
		];
	}
}
