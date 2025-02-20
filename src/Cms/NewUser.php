<?php

namespace Kirby\Cms;

use Kirby\Content\MemoryStorage;
use Kirby\Http\Idn;

class NewUser extends User
{
	use NewModelFixes;

	/**
	 * Creates a new User from the given props and returns a new User object
	 */
	public static function create(array $props): User
	{
		$input = $props;
		$props = self::normalizeProps($props);

		// create the instance without content or translations
		// to avoid that the user is created in memory storage
		$user = static::factory([
			...$props,
			'content'      => null,
			'translations' => null
		]);

		// merge the content with the defaults
		$props['content'] = [
			...$user->createDefaultContent(),
			...$props['content'],
		];

		// keep the initial storage class
		$storage = $user->storage()::class;

		// make sure that the temporary user is stored in memory
		$user->changeStorage(MemoryStorage::class);

		// inject the content
		$user->setContent($props['content']);

		// inject the translations
		$user->setTranslations($props['translations'] ?? null);

		// run the hook
		return $user->commit('create', ['user' => $user, 'input' => $input], function ($user) use ($storage) {
			$user->writeCredentials([
				'email'    => $user->email(),
				'language' => $user->language(),
				'name'     => $user->name()->value(),
				'role'     => $user->role()->id(),
			]);

			$user->writePassword($user->password());
			$user->changeStorage($storage);

			// write the user data
			return $user;
		});
	}

	protected static function normalizeProps(array $props): array
	{
		$content = $props['content'] ?? [];
		$role    = $props['role']    ?? 'default';

		if (isset($props['email']) === true) {
			$props['email'] = Idn::decodeEmail($props['email']);
		}

		if (isset($props['password']) === true) {
			$props['password'] = static::hashPassword($props['password']);
		}

		return [
			...$props,
			'content' => $content,
			'model'   => $props['model'] ?? $role,
			'role'    => $role
		];
	}
}
