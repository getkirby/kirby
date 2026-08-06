<?php

namespace Kirby\Guards;

use Kirby\Cms\File;
use Kirby\Cms\Model;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\File as BaseFile;

/**
 * Bundles all guards for a `$file` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @method FileAbilities abilities()
 * @method FilePermissions permissions()
 * @method FileValidators validators()
 */
class FileGuards extends ModelGuards
{
	/**
	 * @var File
	 */
	protected Model $model;

	/**
	 * @var FileAbilities
	 */
	protected ModelAbilities $abilities;

	/**
	 * @var FilePermissions
	 */
	protected ModelPermissions $permissions;

	/**
	 * @var FileValidators
	 */
	protected ModelValidators $validators;

	public function __construct(
		File $model,
		User $user
	) {
		parent::__construct(
			abilities: new FileAbilities(
				model: $model,
				user: $user
			),
			model: $model,
			permissions: new FilePermissions(
				model: $model,
				user: $user
			),
			user: $user,
			validators: new FileValidators(
				model: $model,
				user: $user
			),
		);
	}

	/**
	 * Checks if the file can be sorted
	 *
	 * @throws PermissionException If the user is not allowed to sort the file
	 */
	protected function ensureToChangeSort(int $sort): void
	{
		$this->ensureAvailable('sort');
	}

	/**
	 * Checks if the file can be created
	 *
	 * @throws DuplicateException If a file with the same name exists
	 * @throws PermissionException If the user is not allowed to create the file
	 */
	protected function ensureToCreate(BaseFile $upload): void
	{
		// uploading the exact same file again changes nothing
		// and therefore needs no checks at all
		if ($this->model->isSameAs($upload) === true) {
			return;
		}

		$this->ensureAvailable('create');
		$this->validators()->ensure('create', $upload);
	}
}
