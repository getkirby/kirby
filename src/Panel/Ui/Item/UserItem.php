<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\User;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 *
 * @extends \Kirby\Panel\Ui\Item\ModelItem<\Kirby\Cms\User, \Kirby\Panel\User>
 */
class UserItem extends ModelItem
{
	public function __construct(
		User $user,
		string|array|false|null $image = [],
		string|null $info = '{{ user.role.title }}',
		string|null $layout = null,
		string|null $text = null,
	) {
		parent::__construct(
			model: $user,
			image: $image,
			info: $info,
			layout: $layout,
			text: $text ?? '{{ user.username }}',
		);
	}
}
