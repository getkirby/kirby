<?php

namespace Kirby\Guards;

use Kirby\Cms\File;
use Kirby\Cms\User;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\File as BaseFile;

/**
 * Bundles all guards for a `$file` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelGuards<File, FileAbilities, FilePermissions, FileValidators>
 */
class FileGuards extends ModelGuards
{
	public function __construct(
		File $model,
		User $user
	) {
		parent::__construct($model, $user);

		$this->abilities   = new FileAbilities($model, $user);
		$this->permissions = new FilePermissions($model, $user);
		$this->validators  = new FileValidators($model, $user);
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
	 * @throws Exception If the file does not match the `accept` rules of its blueprint
	 * @throws InvalidArgumentException If the name, extension, MIME type or contents of the file are invalid
	 * @throws PermissionException If the user is not allowed to create the file
	 */
	protected function ensureToCreate(BaseFile $upload): void
	{
		// uploading the exact same file again changes nothing
		// and therefore needs no checks at all
		if ($this->model->isIdentical($upload) === true) {
			return;
		}

		$this->ensureAvailable('create');
		$this->validators()->ensure('create', $upload);
	}
}
