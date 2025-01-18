<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\File as BaseFile;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Validators for all file actions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FileRules
{
	/**
	 * Validates if the filename can be changed
	 *
	 * @throws \Kirby\Exception\DuplicateException If a file with this name exists
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to rename the file
	 */
	public static function changeName(File $file, string $name): void
	{
		if ($file->permissions()->can('changeName') !== true) {
			throw new PermissionException(
				key: 'file.changeName.permission',
				data: ['filename' => $file->filename()]
			);
		}

		if (Str::length($name) === 0) {
			throw new InvalidArgumentException(
				key: 'file.changeName.empty'
			);
		}

		$parent    = $file->parent();
		$duplicate = $parent->files()->not($file)->findBy('filename', $name . '.' . $file->extension());

		if ($duplicate) {
			throw new DuplicateException(
				key: 'file.duplicate',
				data: ['filename' => $duplicate->filename()]
			);
		}
	}

	/**
	 * Validates if the file can be sorted
	 */
	public static function changeSort(File $file, int $sort): void
	{
		if ($file->permissions()->can('sort') !== true) {
			throw new PermissionException(
				key: 'file.sort.permission',
				data: ['filename' => $file->filename()]
			);
		}
	}

	/**
	 * Validates if the template of the file can be changed
	 *
	 * @throws \Kirby\Exception\LogicException If the template of the page cannot be changed at all
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to change the template
	 */
	public static function changeTemplate(File $file, string $template): void
	{
		if ($file->permissions()->can('changeTemplate') !== true) {
			throw new PermissionException(
				key: 'file.changeTemplate.permission',
				data: ['id' => $file->id()]
			);
		}

		$blueprints = $file->blueprints();

		// ensure that the $template is a valid blueprint
		// option for this file
		if (
			count($blueprints) <= 1 ||
			in_array($template, array_column($blueprints, 'name'), true) === false
		) {
			throw new LogicException(
				key: 'file.changeTemplate.invalid',
				data: [
					'id' 		 => $file->id(),
					'template'   => $template,
					'blueprints' => implode(', ', array_column($blueprints, 'name'))
				]
			);
		}
	}

	/**
	 * Validates if the file can be created
	 *
	 * @throws \Kirby\Exception\DuplicateException If a file with the same name exists
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to create the file
	 */
	public static function create(File $file, BaseFile $upload): void
	{
		// We want to ensure that we are not creating duplicate files.
		// If a file with the same name already exists
		if ($file->exists() === true) {
			// $file will be based on the props of the new file,
			// to compare templates, we need to get the props of
			// the already existing file from meta content file
			$existing = $file->parent()->file($file->filename());

			// if the new upload is the exact same file
			// and uses the same template, we can continue
			if (
				$file->sha1() === $upload->sha1() &&
				$file->template() === $existing->template()
			) {
				return;
			}

			// otherwise throw an error for duplicate file
			throw new DuplicateException(
				key: 'file.duplicate',
				data: [
					'filename' => $file->filename()
				]
			);
		}

		if ($file->permissions()->can('create') !== true) {
			throw new PermissionException(
				message: 'The file cannot be created'
			);
		}

		static::validFile($file, $upload->mime());

		$upload->match($file->blueprint()->accept());
		$upload->validateContents(true);
	}

	/**
	 * Validates if the file can be deleted
	 *
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to delete the file
	 */
	public static function delete(File $file): void
	{
		if ($file->permissions()->can('delete') !== true) {
			throw new PermissionException(
				message: 'The file cannot be deleted'
			);
		}
	}

	/**
	 * Validates if the file can be replaced
	 *
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to replace the file
	 * @throws \Kirby\Exception\InvalidArgumentException If the file type of the new file is different
	 */
	public static function replace(File $file, BaseFile $upload): void
	{
		if ($file->permissions()->can('replace') !== true) {
			throw new PermissionException(
				message: 'The file cannot be replaced'
			);
		}

		static::validMime($file, $upload->mime());

		if (
			(string)$upload->mime() !== (string)$file->mime() &&
			(string)$upload->extension() !== (string)$file->extension()
		) {
			throw new InvalidArgumentException(
				key: 'file.mime.differs',
				data: ['mime' => $file->mime()]
			);
		}

		$upload->match($file->blueprint()->accept());
		$upload->validateContents(true);
	}

	/**
	 * Validates if the file can be updated
	 *
	 * @throws \Kirby\Exception\PermissionException If the user is not allowed to update the file
	 */
	public static function update(File $file, array $content = []): void
	{
		if ($file->permissions()->can('update') !== true) {
			throw new PermissionException(
				message: 'The file cannot be updated'
			);
		}
	}

	/**
	 * Validates the file extension
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the extension is missing or forbidden
	 */
	public static function validExtension(File $file, string $extension): void
	{
		// make it easier to compare the extension
		$extension = strtolower($extension);

		if (empty($extension) === true) {
			throw new InvalidArgumentException(
				key: 'file.extension.missing',
				data: ['filename' => $file->filename()]
			);
		}

		if (
			Str::contains($extension, 'php') !== false ||
			Str::contains($extension, 'phar') !== false ||
			Str::contains($extension, 'pht') !== false
		) {
			throw new InvalidArgumentException(
				key: 'file.type.forbidden',
				data: ['type' => 'PHP']
			);
		}

		if (Str::contains($extension, 'htm') !== false) {
			throw new InvalidArgumentException(
				key: 'file.type.forbidden',
				data: ['type' => 'HTML']
			);
		}

		if (V::in($extension, ['exe', App::instance()->contentExtension()]) !== false) {
			throw new InvalidArgumentException(
				key: 'file.extension.forbidden',
				data: ['extension' => $extension]
			);
		}
	}

	/**
	 * Validates the extension, MIME type and filename
	 *
	 * @param string|false|null $mime If not passed, the MIME type is detected from the file,
	 *                                if `false`, the MIME type is not validated for performance reasons
	 * @throws \Kirby\Exception\InvalidArgumentException If the extension, MIME type or filename is missing or forbidden
	 */
	public static function validFile(
		File $file,
		string|false|null $mime = null
	): void {
		// request to skip the MIME check for performance reasons
		if ($mime !== false) {
			static::validMime($file, $mime ?? $file->mime());
		}

		static::validExtension($file, $file->extension());
		static::validFilename($file, $file->filename());
	}

	/**
	 * Validates the filename
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the filename is missing or forbidden
	 */
	public static function validFilename(File $file, string $filename): void
	{
		// make it easier to compare the filename
		$filename = strtolower($filename);

		// check for missing filenames
		if (empty($filename)) {
			throw new InvalidArgumentException(
				key: 'file.name.missing'
			);
		}

		// Block htaccess files
		if (Str::startsWith($filename, '.ht')) {
			throw new InvalidArgumentException(
				key: 'file.type.forbidden',
				data: ['type' => 'Apache config']
			);
		}

		// Block invisible files
		if (Str::startsWith($filename, '.')) {
			throw new InvalidArgumentException(
				key: 'file.type.forbidden',
				data: ['type' => 'invisible']
			);
		}
	}

	/**
	 * Validates the MIME type
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the MIME type is missing or forbidden
	 */
	public static function validMime(File $file, string|null $mime = null): void
	{
		// make it easier to compare the mime
		$mime = strtolower($mime ?? '');

		if (empty($mime)) {
			throw new InvalidArgumentException(
				key: 'file.mime.missing',
				data: ['filename' => $file->filename()]
			);
		}

		if (Str::contains($mime, 'php')) {
			throw new InvalidArgumentException(
				key: 'file.type.forbidden',
				data: ['type' => 'PHP']
			);
		}

		if (V::in($mime, ['text/html', 'application/x-msdownload'])) {
			throw new InvalidArgumentException(
				key: 'file.mime.forbidden',
				data:['mime' => $mime]
			);
		}
	}
}
