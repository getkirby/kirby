<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;

/**
 * Bundles all guards for a `$page` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
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
	 * @throws InvalidArgumentException If the given status is invalid
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
	 * @throws PermissionException If the user is not allowed to change the status or the page cannot be converted to a draft
	 */
	protected function ensureToChangeStatusToDraft(): void
	{
		$this->ensureAvailable('changeStatusToDraft');
	}

	/**
	 * Checks if the status of the page can be changed to listed
	 *
	 * @throws InvalidArgumentException If the given position is invalid
	 * @throws PermissionException If the user is not allowed to change the status or the status for the page cannot be changed by any user
	 */
	protected function ensureToChangeStatusToListed(int $position): void
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
	 * @throws PermissionException If the user is not allowed to change the status
	 */
	protected function ensureToChangeStatusToUnlisted(): void
	{
		$this->ensureToPublish();
	}

	/**
	 * Checks if the page can be created
	 *
	 * @throws DuplicateException If the same page or a draft already exists
	 * @throws InvalidArgumentException If the slug is invalid
	 * @throws PermissionException If the user is not allowed to create this page
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
	 * Checks if the page can be moved to the given parent
	 *
	 * @throws DuplicateException If the new parent already has a page with this slug
	 * @throws LogicException If the page cannot be moved to the given parent
	 * @throws PermissionException If the user is not allowed to move the page
	 */
	protected function ensureToMove(Site|Page $parent): void
	{
		// if nothing changes, there's no need for checks
		if ($parent->is($this->model->parent()) === true) {
			return;
		}

		$this->ensureAvailable('move');
		$this->validators()->ensure('move', $parent);
	}

	/**
	 * Checks if the page can be published
	 * (status change from draft to listed or unlisted)
	 *
	 * @throws LogicException If the page is incomplete and cannot be published
	 * @throws PermissionException If the user is not allowed to change the status
	 */
	protected function ensureToPublish(): void
	{
		$this->ensureAvailable('publish');
	}
}
