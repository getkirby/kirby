<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Panel\Ui\Item;

/**
 * Stand-in for a model that exists, but that the current user is not
 * allowed to list. It only echoes back the ID that is already stored
 * in the content file, so that the model is neither disclosed nor
 * dropped from the value.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class ProtectedItem extends Item
{
	public function __construct(
		protected string $id,
		string|null $layout = null,
	) {
		parent::__construct(
			text: '–',
			image: [
				'back'  => 'pattern',
				'color' => 'gray-500',
				'cover' => false,
				'icon'  => 'protected'
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
