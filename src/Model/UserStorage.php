<?php

namespace Kirby\Model;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Uuid\Uuid;
use Throwable;

class UserStorage extends Storage
{
	/**
	 * @var User
	 */
	protected Model $model;

	public function __construct(
		User $user,
	) {
		parent::__construct($user);
	}

	public function changeEmail(UserMeta $oldMeta, UserMeta $newMeta): void
	{
		static::writeMeta($newMeta);
	}

	public function changeLanguage(UserMeta $oldMeta, UserMeta $newMeta): void
	{
		static::writeMeta($newMeta);
	}

	public function changeName(UserMeta $oldMeta, UserMeta $newMeta): void
	{
		static::writeMeta($newMeta);
	}

	public function changePassword(UserMeta $oldMeta, UserMeta $newMeta): void
	{
		static::writePassword($newMeta);
	}

	public function changeRole(UserMeta $oldMeta, UserMeta $newMeta): void
	{
		static::writeMeta($newMeta);
	}

	public static function create(UserMeta $meta): User
	{
		// create the new identifier
		$meta->identifier = static::createIdentifier($meta);

		// check if the user directory already exists
		if (is_dir($meta->identifier) === true) {
			throw new DuplicateException('The user already exists');
		}

		// create the new directory
		Dir::make($meta->identifier);

		static::writeMeta($meta);

		// create the new content file
		Data::write(static::contentFile($meta), []);

		return static::find(User::class, $meta->identifier);
	}

	protected static function createIdentifier(UserMeta $meta): string
	{
		// create the new identifier
		return App::instance()->root('accounts') . '/' . $meta->uuid;
	}

	protected static function dir(UserMeta $meta): string
	{
		return dirname($meta->identifier) . '/' . $meta->uuid;
	}

	protected static function contentFile($meta): string
	{
		return $meta->identifier . '/user.txt';
	}

	public static function find(string $class, string $identifier): Model|null
	{
		if (is_dir($identifier) === false) {
			return null;
		}

		$meta = new UserMeta(
			identifier: $identifier,
			uuid: basename($identifier),
		);

		$info = require static::metaFile($meta);

		$meta->email    = $info['email'] ?? null;
		$meta->language = $info['language'] ?? null;
		$meta->name     = $info['name'] ?? null;
		$meta->role     = $info['role'] ?? 'default';

		// get the password from the .htpasswd file
		$meta->password = static::readPassword($meta);

		return new $class(
			identifier: $meta->identifier,
			email: $meta->email,
			language: $meta->language,
			name: $meta->name,
			password: $meta->password,
			role: $meta->role,
			uuid: $meta->uuid,
		);
	}

	protected static function metaFile(UserMeta $meta): string
	{
		return $meta->identifier . '/index.php';
	}

	protected static function passwordFile(UserMeta $meta): string
	{
		return $meta->identifier . '/.htpasswd';
	}

	protected static function readPassword(UserMeta $meta): string
	{
		return F::read(static::passwordFile($meta));
	}

	protected static function writeMeta(UserMeta $meta): void
	{
		$file = static::metaFile($meta);

		Data::write($file, [
			'email'    => $meta->email,
			'language' => $meta->language,
			'name'     => $meta->name,
			'role'     => $meta->role,
		]);
	}

	protected static function writePassword(UserMeta $meta): string
	{
		return F::write(static::passwordFile($meta), $meta->password);
	}
}
