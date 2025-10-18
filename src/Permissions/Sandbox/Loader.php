<?php

namespace Kirby\Permissions;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\LanguageVariable;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;

class Loader
{
	protected static function classForModel(ModelWithContent $model): string
	{
		return match (true) {
			$model instanceof File             => FilePermissions::class,
			$model instanceof Language         => LanguagePermissions::class,
			$model instanceof LanguageVariable => LanguageVariablePermissions::class,
			$model instanceof Page             => PagePermissions::class,
			$model instanceof Site             => SitePermissions::class,
			$model instanceof User             => UserPermissions::class,
		};
	}

	public static function forModel(ModelWithContent $model, User $user): ModelPermissions
	{
		// get the matching permission class
		$class = static::classForModel($model);

		if ($user->isNobody() === true) {
			return $class::forNobody();
		}

		$options = $model->blueprint()->options();

		dump($options);

		$options = array_map(function ($option) use ($user) {
			return static::roleMatrixToPermission($option, $user->role()->name());
		}, $options);

		return $class::from($options);
	}

	public static function forUser(User $user): Permissions
	{
		if ($user->isAdmin() === true) {
			return Permissions::forAdmin();
		}

		if ($user->isNobody() === true) {
			return Permissions::forNobody();
		}

		$permissions = $user->blueprint()->permissions();

		return Permissions::from($permissions);
	}

	public static function roleMatrixToPermission(array|bool|null $matrix, string $role = '*'): bool|null
	{
		if ($matrix === null) {
			return null;
		}

		if (is_bool($matrix) === true) {
			return $matrix;
		}

		if (isset($matrix[$role]) === true) {
			return $matrix[$role];
		}

		return $matrix['*'] ?? true;
	}
}
