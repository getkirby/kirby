<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Panel\Ui\Item;

/**
 * Stand-in for a model that does not exist (anymore). It shows
 * the ID that is stored in the content file, so that the model
 * is not silently dropped from the value but can be removed.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class MissingItem extends Item
{
	public function __construct(
		protected string $id,
		string|null $layout = null,
	) {
		parent::__construct(
			text: $id,
			image: [
				'back'  => 'pattern',
				'color' => 'red-600',
				'cover' => false,
				'icon'  => 'alert'
			],
			layout: $layout
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'id'          => $this->id,
			'link'        => false,
			'permissions' => [],
			'uuid'        => $this->id,
		];
	}
}
