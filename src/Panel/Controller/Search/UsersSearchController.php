<?php

namespace Kirby\Panel\Controller\Search;

use Kirby\Cms\Users;
use Kirby\Toolkit\Escape;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UsersSearchController extends ModelsSearchController
{
	public function models(): Users
	{
		return $this->kirby->users()->search($this->query);
	}

	/**
	 * @param \Kirby\Cms\User $model
	 */
	public function result($model): array
	{
		return [
			'image' => $model->panel()->image(),
			'text'  => Escape::html($model->username()),
			'link'  => $model->panel()->url(true),
			'info'  => Escape::html($model->role()->title()),
			'uuid'  => $model->uuid()->toString(),
		];
	}
}
