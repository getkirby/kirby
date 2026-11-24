<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\User;
use Kirby\Panel\Ui\Item\UserItem;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserItemsRequestController extends ModelItemsRequestController
{
	protected const ITEM_CLASS = UserItem::class;

	protected function model(string $id): User|null
	{
		return $this->kirby->user($id);
	}
}
