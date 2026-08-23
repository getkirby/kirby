<?php

namespace Kirby\Cms;

use Kirby\Filesystem\File as BaseFile;

/**
 * Validators for all file actions
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @deprecated 6.0.0 Use `$file->guards()` instead
 */
class FileRules
{
	public static function changeName(File $file, string $name): void
	{
		$file->guards()->ensureExecutable('changeName', $name);
	}

	public static function changeSort(File $file, int $sort): void
	{
		$file->guards()->ensureExecutable('changeSort', $sort);
	}

	public static function changeTemplate(File $file, string $template): void
	{
		$file->guards()->ensureExecutable('changeTemplate', $template);
	}

	public static function create(File $file, BaseFile $upload): void
	{
		$file->guards()->ensureExecutable('create', $upload);
	}

	public static function delete(File $file): void
	{
		$file->guards()->ensureExecutable('delete');
	}

	public static function replace(File $file, BaseFile $upload): void
	{
		$file->guards()->ensureExecutable('replace', $upload);
	}

	public static function update(File $file, array $content = []): void
	{
		$file->guards()->ensureExecutable('update', $content);
	}

	public static function validExtension(File $file, string $extension): void
	{
		$file->guards()->validators()->validateExtension($extension);
	}

	public static function validFile(
		File $file,
		string|false|null $mime = null
	): void {
		$file->guards()->validators()->validateFile($mime);
	}

	public static function validFilename(File $file, string $filename): void
	{
		$file->guards()->validators()->validateFilename($filename);
	}

	public static function validMime(File $file, string|null $mime = null): void
	{
		$file->guards()->validators()->validateMime($mime);
	}
}
