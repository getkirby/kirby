<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class UserItem extends ModelItem
{
	/**
	 * @var \Kirby\Cms\User
	 */
	protected ModelWithContent $model;

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
