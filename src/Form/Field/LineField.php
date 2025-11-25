<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Line field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LineField extends BaseField
{
	use Mixin\Width;

	public function __construct(
		string|null $name = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			name:  $name,
			when:  $when,
		);

		$this->setWidth($width);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'width' => $this->width()
		];
	}
}
