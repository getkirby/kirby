<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\Users;
use Kirby\Panel\Ui\Item\UserItem;
use Override;

/**
 * Controls the search requests for users
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UsersSearchController extends ModelsSearchController
{
	/**
	 * @param \Kirby\Cms\User $model
	 */
	#[Override]
	public function item($model): UserItem
	{
		return new UserItem(user: $model);
	}

	#[Override]
	public function models(): Users
	{
		return $this->kirby->users()->search($this->query);
	}
}
