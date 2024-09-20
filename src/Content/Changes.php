<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Files;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\Users;
use Kirby\Toolkit\A;

/**
 * The Changes class tracks changed models
 * in the Site's changes field.
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Changes
{
	/**
	 * Access helper for the field, in which changes are stored
	 */
	public function field(): Field
	{
		return $this
			->site()
			->version(VersionId::published())
			->content()
			->get('changes');
	}

	/**
	 * Return all files with unsaved changes
	 */
	public function files(): Files
	{
		return $this->field()->toFiles();
	}

	/**
	 * Return all pages with unsaved changes
	 */
	public function pages(): Pages
	{
		return $this->field()->toPages();
	}

	/**
	 * Access helper for the site object
	 */
	public function site(): Site
	{
		return App::instance()->site();
	}

	/**
	 * Add a new model to the list of unsaved changes
	 */
	public function track(ModelWithContent $model): void
	{
		$changes = $this->field()->yaml();
		$changes[] = (string)$model->uuid();

		$this->update($changes);
	}

	/**
	 * Remove a model from the list of unsaved changes
	 */
	public function untrack(ModelWithContent $model): void
	{
		$changes = A::filter(
			$this->field()->yaml(),
			fn ($uuid) => $uuid !== (string)$model->uuid()
		);

		$this->update($changes);
	}

	/**
	 * Update the changes field
	 */
	public function update(array $changes): void
	{
		$changes = array_unique($changes);
		$changes = array_values($changes);

		$this
			->site()
			->version(VersionId::published())
			->update([
				'changes' => $changes
			]);
	}

	/**
	 * Return all users with unsaved changes
	 */
	public function users(): Users
	{
		return $this->field()->toUsers();
	}
}
