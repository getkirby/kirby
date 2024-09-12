<?php

namespace Kirby\Content;

use Kirby\Cms\Files;
use Kirby\Cms\Pages;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Cms\Users;

/**
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Changes
{

	public function __construct(
		protected User $user
	) {
	}

	/**
	 * Return all files with unsaved changes
	 */
	public function files(): Files
	{
		return $this->user->content()->get('changes')->toFiles();
	}

	/**
	 * Return all pages with unsaved changes
	 */
	public function pages(): Pages
	{
		return $this->user->content()->get('changes')->toPages();
	}

	/**
	 * Add a new model to the list of unsaved changes
	 */
	public function track(ModelWithContent $model): void
	{
		$changes = $this->user->content()->get('changes')->yaml();
		$changes[] = (string)$model->uuid();

		// make sure that each UUID is only stored once
		$changes = array_unique($changes);

		$this->user->update([
			'changes' => array_values($changes)
		]);
	}

	/**
	 * Remove a model from the list of unsaved changes
	 */
	public function untrack(ModelWithContent $model)
	{
		$changes = $this->user->content()->get('changes')->yaml();
		$uuid    = (string)$model->uuid();
		$index   = array_search($uuid, $changes);

		if ($index !== false) {
			unset($changes[$index]);
		}

		$this->user->update([
			'changes' => array_values($changes)
		]);
	}

	/**
	 * Return all users with unsaved changes
	 */
	public function users(): Users
	{
		return $this->user->content()->get('changes')->toUsers();
	}
}
