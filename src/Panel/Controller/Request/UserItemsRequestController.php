<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\User;
use Kirby\Panel\Controller\RequestController;
use Kirby\Panel\Ui\Item\UserItem;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserItemsRequestController extends RequestController
{
	protected function item(User $user): UserItem
	{
		return new UserItem(
			user: $user,
			info: $this->request->get('info'),
			layout: $this->request->get('layout', 'list'),
			text: $this->request->get('text'),
		);
	}

	public function load(): array
	{
		$ids   = $this->request->get('items', '');
		$ids   = Str::split($ids);
		$users = A::map($ids, fn ($id) => $this->kirby->user($id));
		$items = A::map($users, fn ($user) => $this->item($user)->props());
		return ['items' => $items];
	}
}
