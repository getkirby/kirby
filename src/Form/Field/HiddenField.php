<?php

namespace Kirby\Form\Field;

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
	public function __construct(
		mixed $default = null,
		string|null $name = null,
		bool|null $translate = null,
		array|null $when = null,
	) {
		parent::__construct(
			name: $name,
			when: $when,
		);

		$this->setDefault($default);
		$this->setTranslate($translate);
	}

	public function hasValue(): bool
	{
		return true;
	}

	public function isHidden(): bool
	{
		return true;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'default'   => $this->default(),
			'translate' => $this->translate(),
		];
	}
}
