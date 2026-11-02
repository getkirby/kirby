<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;

/**
 * Bundles all guards for a `$page` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @method PageAbilities abilities()
 * @method PagePermissions permissions()
 * @method PageValidators validators()
 */
class PageGuards extends ModelGuards
{
	/**
	 * @var Page
	 */
	protected Model $model;

	/**
	 * @var PageAbilities
	 */
	protected ModelAbilities $abilities;

	/**
	 * @var PagePermissions
	 */
	protected ModelPermissions $permissions;

	/**
	 * @var PageValidators
	 */
	protected ModelValidators $validators;

	public function __construct(
		Page $model,
		User $user
	) {
		parent::__construct(
			abilities: new PageAbilities(
				model: $model,
				user: $user
			),
			model: $model,
			permissions: new PagePermissions(
				model: $model,
				user: $user
			),
			user: $user,
			validators: new PageValidators(
				model: $model,
				user: $user
			),
		);
	}

	/**
	 * Checks if the sorting number of the page can be changed
	 *
	 * @throws InvalidArgumentException If the given number is invalid
	 */
	protected function ensureToChangeNum(int|null $num = null): void
	{
		$this->validators()->ensure('changeNum', $num);
	}

	/**
	 * Checks if the status of the page can be changed
	 *
	 * @throws AbilityException If the status of the page cannot be changed by any user
	 * @throws InvalidArgumentException If the given status or position is invalid
	 * @throws PermissionException If the user is not allowed to change the status or to sort the page
	 */
	protected function ensureToChangeStatus(
		string $status,
		int|null $position = null
	): void {
		$this->validators()->ensure('changeStatus', $status, $position);

		match ($status) {
			'draft'    => $this->ensureToChangeStatusToDraft(),
			'listed'   => $this->ensureToChangeStatusToListed($position),
			'unlisted' => $this->ensureToChangeStatusToUnlisted(),
			default    => throw new InvalidArgumentException(
				key: 'page.status.invalid'
			)
		};
	}

	/**
	 * Checks if the page can be converted to a draft
	 *
	 * @throws AbilityException If the page cannot be converted to a draft by any user
	 * @throws PermissionException If the user is not allowed to change the status
	 */
	protected function ensureToChangeStatusToDraft(): void
	{
		$this->ensureAvailable('changeStatusToDraft');
	}

	/**
	 * Checks if the status of the page can be changed to listed
	 *
	 * @throws AbilityException If the status of the page cannot be changed or the page cannot be sorted by any user
	 * @throws InvalidArgumentException If the given position is invalid
	 * @throws PermissionException If the user is not allowed to change the status or to sort the page
	 */
	protected function ensureToChangeStatusToListed(int|null $position = null): void
	{
		// no need to check for status changing permissions,
		// instead we need to check for sorting permissions
		if ($this->model->isListed() === true) {
			$this->ensureAvailable('sort');

			return;
		}

		$this->ensureToPublish();

		$this->validators()->ensure('changeStatusToListed', $position);
	}

	/**
	 * Checks if the status of the page can be changed to unlisted
	 *
	 * @throws AbilityException If the status of the page cannot be changed by any user
	 * @throws PermissionException If the user is not allowed to change the status
	 */
	protected function ensureToChangeStatusToUnlisted(): void
	{
		$this->ensureToPublish();
	}

	/**
	 * Checks if the page can be created
	 *
	 * @throws AbilityException If a listed or unlisted page cannot be published, e.g. the error page
	 * @throws DuplicateException If the same page or a draft already exists
	 * @throws InvalidArgumentException If the slug is invalid
	 * @throws PermissionException If the user is not allowed to create this page or to publish it
	 */
	protected function ensureToCreate(): void
	{
		$this->ensureAvailable('create');

		// creating a non-draft bypasses the normal publish flow;
		// enforce the same rules
		if ($this->model->isDraft() === false) {
			$this->ensureToPublish();
		}

		$this->validators()->ensure('create');
	}

	/**
	 * Checks if the page can be deleted
	 *
	 * @throws AbilityException If the page or one of its subpages cannot be deleted by any user
	 * @throws LogicException If the page has children and should not be force-deleted
	 * @throws PermissionException If the user is not allowed to delete the page, one of its files or subpages
	 */
	protected function ensureToDelete(bool $force = false): void
	{
		$this->ensureAvailable('delete');
		$this->validators()->ensure('delete', $force);

		// files and subpages get deleted with the page, so check them
		// upfront instead of failing halfway through the deletion
		foreach ($this->model->files() as $file) {
			$file->guards()->ensureExecutable('delete');
		}

		foreach ($this->model->childrenAndDrafts() as $child) {
			$child->guards()->ensureExecutable('delete', true);
		}
	}

	/**
	 * Checks if the page can be moved to the given parent
	 *
	 * @throws AbilityException If the page cannot be moved by any user
	 * @throws DuplicateException If the new parent already has a page with this slug
	 * @throws InvalidArgumentException If the slug is reserved at the new parent
	 * @throws LogicException If the page cannot be moved to the given parent
	 * @throws PermissionException If the user is not allowed to move the page or the new parent does not accept its template
	 */
	protected function ensureToMove(Site|Page $parent): void
	{
		// if nothing changes, there's no need for checks
		if ($this->model->parentModel()->is($parent) === true) {
			return;
		}

		$this->ensureAvailable('move');
		$this->validators()->ensure('move', $parent);
	}

	/**
	 * Checks if the page can be published
	 * (status change from draft to listed or unlisted)
	 *
	 * @throws AbilityException If the page is incomplete or its status cannot be changed by any user
	 * @throws PermissionException If the user is not allowed to change the status
	 */
	protected function ensureToPublish(): void
	{
		$this->ensureAvailable('publish');
	}
}
