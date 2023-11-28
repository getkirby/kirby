<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Str;

/**
 * The Find class is used in the API and
 * the Panel to find models and parents
 * based on request paths
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Find
{
	/**
	 * Returns the file object for the given
	 * parent path and filename
	 *
	 * @param string $path Path to file's parent model
	 * @throws \Kirby\Exception\NotFoundException if the file cannot be found
	 */
	public static function file(
		string $path,
		string $filename
	): File|null {
		$filename = urldecode($filename);
		$parent   = empty($path) ? null : static::parent($path);
		$file     = App::instance()->file($filename, $parent);

		if ($file?->isAccessible() === true) {
			return $file;
		}

		throw new NotFoundException([
			'key'  => 'file.notFound',
			'data' => [
				'filename' => $filename
			]
		]);
	}

	/**
	 * Returns the language object for the given code
	 *
	 * @param string $code Language code
	 * @throws \Kirby\Exception\NotFoundException if the language cannot be found
	 */
	public static function language(string $code): Language|null
	{
		if ($language = App::instance()->language($code)) {
			return $language;
		}

		throw new NotFoundException([
			'key'  => 'language.notFound',
			'data' => [
				'code' => $code
			]
		]);
	}

	/**
	 * Returns the page object for the given id
	 *
	 * @param string $id Page's id
	 * @throws \Kirby\Exception\NotFoundException if the page cannot be found
	 */
	public static function page(string $id): Page|null
	{
		// decode API ID encoding
		$id    = str_replace(['+', ' '], '/', $id);
		$kirby = App::instance();
		$page  = $kirby->page($id, null, true);

		if ($page?->isAccessible() === true) {
			return $page;
		}

		throw new NotFoundException([
			'key'  => 'page.notFound',
			'data' => [
				'slug' => $id
			]
		]);
	}

	/**
	 * Returns the model's object for the given path
	 *
	 * @param string $path Path to parent model
	 * @throws \Kirby\Exception\InvalidArgumentException if the model type is invalid
	 * @throws \Kirby\Exception\NotFoundException if the model cannot be found
	 */
	public static function parent(string $path): ModelWithContent
	{
		$path       = trim($path, '/');
		$modelType  = in_array($path, ['site', 'account']) ? $path : trim(dirname($path), '/');
		$modelTypes = [
			'site'    => 'site',
			'users'   => 'user',
			'pages'   => 'page',
			'account' => 'account'
		];

		$modelName = $modelTypes[$modelType] ?? null;

		if (Str::endsWith($modelType, '/files') === true) {
			$modelName = 'file';
		}

		$kirby = App::instance();

		$model = match ($modelName) {
			'site'    => $kirby->site(),
			'account' => static::user(),
			'page'    => static::page(basename($path)),
			// regular expression to split the path at the last
			// occurrence of /files/ which separates parent path
			// and filename
			'file'    => static::file(...preg_split('$.*\K(/files/)$', $path)),
			'user'    => $kirby->user(basename($path)),
			default   => throw new InvalidArgumentException('Invalid model type: ' . $modelType)
		};

		return $model ?? throw new NotFoundException([
			'key' => $modelName . '.undefined'
		]);
	}

	/**
	 * Returns the user object for the given id or
	 * returns the current authenticated user if no
	 * id is passed
	 *
	 * @param string|null $id User's id
	 * @throws \Kirby\Exception\NotFoundException if the user for the given id cannot be found
	 */
	public static function user(string $id = null): User|null
	{
		// account is a reserved word to find the current
		// user. It's used in various API and area routes.
		if ($id === 'account') {
			$id = null;
		}

		$kirby = App::instance();

		// get the authenticated user
		if ($id === null) {
			$user = $kirby->user(
				null,
				$kirby->option('api.allowImpersonation', false)
			);

			return $user ?? throw new NotFoundException([
				'key' => 'user.undefined'
			]);
		}

		// get a specific user by id
		return $kirby->user($id) ?? throw new NotFoundException([
			'key'  => 'user.notFound',
			'data' => [
				'name' => $id
			]
		]);
	}
}
