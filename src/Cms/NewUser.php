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
	public static function create(array|null $props = null): User
	{
		$input = $props ?? [];

		if (isset($props['email']) === true) {
			$props['email'] = Idn::decodeEmail($props['email']);
		}

		if (isset($props['password']) === true) {
			$props['password'] = static::hashPassword($props['password']);
		}

		$content = $props['content'] ?? [];
		$role    = $props['role'] ?? 'default';
		$model   = $props['model'] ?? $role;

		// create the instance without content or translation
		$user = static::factory($props = [
			...$props,
			'content'      => null,
			'model'        => $model,
			'role'         => $role,
			'translations' => null
		]);

		// create a form for the user
		$form = Form::for($user, [
			'language' => Language::ensure('default')->code(),
		]);

		// merge the content back with the defaults
		$props['content'] = [
			...$form->strings(true),
			...$content,
		];

		// keep the initial storage class
		$storage = $user->storage()::class;

		// keep the user in memory until it will be saved
		$user->changeStorage(MemoryStorage::class);

		// inject the content to make this user object usable in the hook
		$user = $user->save($props['content'], 'default');

		// run the hook
		return $user->commit('create', ['user' => $user, 'input' => $input], function ($user, $props) use ($storage) {
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

}
