<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Data\Json;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Form\Form;
use Kirby\Http\Idn;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use SensitiveParameter;
use Throwable;

/**
 * UserActions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait UserActions
{
	/**
	 * Changes the user email address
	 */
	public function changeEmail(string $email): static
	{
		$email = trim($email);

		return $this->commit('changeEmail', ['user' => $this, 'email' => Idn::decodeEmail($email)], function ($user, $email) {
			$user = $user->clone(['email' => $email]);
			$user->updateCredentials(['email' => $email]);

			// update the users collection
			$user->kirby()->users()->set($user->id(), $user);

			return $user;
		});
	}

	/**
	 * Changes the user language
	 */
	public function changeLanguage(string $language): static
	{
		return $this->commit('changeLanguage', ['user' => $this, 'language' => $language], function ($user, $language) {
			$user = $user->clone(['language' => $language]);
			$user->updateCredentials(['language' => $language]);

			// update the users collection
			$user->kirby()->users()->set($user->id(), $user);

			return $user;
		});
	}

	/**
	 * Changes the screen name of the user
	 */
	public function changeName(string $name): static
	{
		$name = trim($name);

		return $this->commit('changeName', ['user' => $this, 'name' => $name], function ($user, $name) {
			$user = $user->clone(['name' => $name]);
			$user->updateCredentials(['name' => $name]);

			// update the users collection
			$user->kirby()->users()->set($user->id(), $user);

			return $user;
		});
	}

	/**
	 * Changes the user password
	 */
	public function changePassword(
		#[SensitiveParameter]
		string $password
	): static {
		return $this->commit('changePassword', ['user' => $this, 'password' => $password], function ($user, $password) {
			$user = $user->clone([
				'password' => $password = User::hashPassword($password)
			]);

			$user->writePassword($password);

			// update the users collection
			$user->kirby()->users()->set($user->id(), $user);

			// keep the user logged in to the current browser
			// if they changed their own password
			// (regenerate the session token, update the login timestamp)
			if ($user->isLoggedIn() === true) {
				$user->loginPasswordless();
			}

			return $user;
		});
	}

	/**
	 * Changes the user role
	 */
	public function changeRole(string $role): static
	{
		return $this->commit('changeRole', ['user' => $this, 'role' => $role], function ($user, $role) {
			$user = $user->clone(['role' => $role]);
			$user->updateCredentials(['role' => $role]);

			// update the users collection
			$user->kirby()->users()->set($user->id(), $user);

			return $user;
		});
	}

	/**
	 * Changes the user's TOTP secret
	 * @since 4.0.0
	 */
	public function changeTotp(
		#[SensitiveParameter]
		string|null $secret
	): static {
		return $this->commit('changeTotp', ['user' => $this, 'secret' => $secret], function ($user, $secret) {
			$this->writeSecret('totp', $secret);

			// keep the user logged in to the current browser
			// if they changed their own TOTP secret
			// (regenerate the session token, update the login timestamp)
			if ($user->isLoggedIn() === true) {
				$user->loginPasswordless();
			}

			return $user;
		});
	}

	/**
	 * Commits a user action, by following these steps
	 *
	 * 1. applies the `before` hook
	 * 2. checks the action rules
	 * 3. commits the action
	 * 4. applies the `after` hook
	 * 5. returns the result
	 *
	 * @throws \Kirby\Exception\PermissionException
	 */
	protected function commit(
		string $action,
		array $arguments,
		Closure $callback
	): mixed {
		if ($this->isKirby() === true) {
			throw new PermissionException(
				message: 'The Kirby user cannot be changed'
			);
		}

		$kirby = $this->kirby();

		// store copy of the model to be passed
		// to the `after` hook for comparison
		$old = $this->hardcopy();

		// check user rules
		$this->rules()->$action(...array_values($arguments));

		// run `before` hook and pass all arguments;
		// the very first argument (which should be the model)
		// is modified by the return value from the hook (if any returned)
		$appliedTo = array_key_first($arguments);
		$arguments[$appliedTo] = $kirby->apply(
			'user.' . $action . ':before',
			$arguments,
			$appliedTo
		);

		// check user rules again, after the hook got applied
		$this->rules()->$action(...array_values($arguments));

		// run closure
		$result = $callback(...array_values($arguments));

		// determine arguments for `after` hook depending on the action
		$argumentsAfter = match ($action) {
			'create' => ['user' => $result],
			'delete' => ['status' => $result, 'user' => $old],
			default  => ['newUser' => $result, 'oldUser' => $old]
		};

		// run `after` hook and apply return to action result
		// (first argument, usually the new model) if anything returned
		$result = $kirby->apply(
			'user.' . $action . ':after',
			$argumentsAfter,
			array_key_first($argumentsAfter)
		);

		$kirby->cache('pages')->flush();

		return $result;
	}

	/**
	 * Creates a new User from the given props and returns a new User object
	 */
	public static function create(array|null $props = null): User
	{
		$data = $props;

		if (isset($props['email']) === true) {
			$data['email'] = Idn::decodeEmail($props['email']);
		}

		if (isset($props['password']) === true) {
			$data['password'] = User::hashPassword($props['password']);
		}

		$props['role'] ??= 'default';
		$props['role']   = $props['model'] = strtolower($props['role']);

		$user = User::factory($data);

		// create a form for the user
		$form = Form::for($user, [
			'values' => $props['content'] ?? []
		]);

		// inject the content
		$user = $user->clone(['content' => $form->strings(true)]);

		// run the hook
		return $user->commit('create', ['user' => $user, 'input' => $props], function ($user, $props) {
			$user->writeCredentials([
				'email'    => $user->email(),
				'language' => $user->language(),
				'name'     => $user->name()->value(),
				'role'     => $user->role()->id(),
			]);

			$user->writePassword($user->password());

			// always create users in the default language
			$languageCode = match ($user->kirby()->multilang()) {
				true  => $user->kirby()->defaultLanguage()->code(),
				false => null
			};

			// add the user to users collection
			$user->kirby()->users()->add($user);

			// write the user data
			return $user->save($user->content()->toArray(), $languageCode);
		});
	}

	/**
	 * Returns a random user id
	 */
	public function createId(): string
	{
		$length = 8;

		do {
			try {
				$id = Str::random($length);
				UserRules::validId($this, $id);
				return $id;

				// we can't really test for a random match
				// @codeCoverageIgnoreStart
			} catch (Throwable) {
				$length++;
			}
		} while (true);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Deletes the user
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	public function delete(): bool
	{
		return $this->commit('delete', ['user' => $this], function ($user) {
			if ($user->exists() === false) {
				return true;
			}

			// delete all public assets for this user
			Dir::remove($user->mediaRoot());

			// delete the user directory
			if (Dir::remove($user->root()) !== true) {
				throw new LogicException(
					message: 'The user directory for "' . $user->email() . '" could not be deleted'
				);
			}

			// remove the user from users collection
			$user->kirby()->users()->remove($user);

			return true;
		});
	}

	/**
	 * Read the account information from disk
	 */
	protected function readCredentials(): array
	{
		$path = $this->root() . '/index.php';

		if (is_file($path) === true) {
			$credentials = F::load($path, allowOutput: false);

			return is_array($credentials) === false ? [] : $credentials;
		}

		return [];
	}

	/**
	 * Reads the user password from disk
	 */
	protected function readPassword(): string|false
	{
		return $this->secret('password') ?? false;
	}

	/**
	 * Reads the secrets from the user secrets file on disk
	 * @since 4.0.0
	 */
	protected function readSecrets(): array
	{
		$file    = $this->secretsFile();
		$secrets = [];

		if (is_file($file) === true) {
			$lines = explode("\n", file_get_contents($file));

			if (isset($lines[1]) === true) {
				$secrets = Json::decode($lines[1]);
			}

			$secrets['password'] = $lines[0];
		}

		// an empty password hash means that no password was set
		if (($secrets['password'] ?? null) === '') {
			unset($secrets['password']);
		}

		return $secrets;
	}

	/**
	 * Updates the user data
	 */
	public function update(
		array|null $input = null,
		string|null $languageCode = null,
		bool $validate = false
	): static {
		$user = parent::update($input, $languageCode, $validate);

		// set auth user data only if the current user is this user
		if ($user->isLoggedIn() === true) {
			$this->kirby()->auth()->setUser($user);
		}

		// update the users collection
		$user->kirby()->users()->set($user->id(), $user);

		return $user;
	}

	/**
	 * This always merges the existing credentials
	 * with the given input.
	 */
	protected function updateCredentials(array $credentials): bool
	{
		// normalize the email address
		if (isset($credentials['email']) === true) {
			$credentials['email'] = Str::lower(trim($credentials['email']));
		}

		return $this->writeCredentials([
			...$this->credentials(),
			...$credentials
		]);
	}

	/**
	 * Writes the account information to disk
	 */
	protected function writeCredentials(array $credentials): bool
	{
		return Data::write($this->root() . '/index.php', $credentials);
	}

	/**
	 * Writes the password to disk
	 */
	protected function writePassword(
		#[SensitiveParameter]
		string|null $password = null
	): bool {
		return $this->writeSecret('password', $password);
	}

	/**
	 * Writes a specific secret to the user secrets file on disk;
	 * `password` is the first line, the rest is stored as JSON
	 * @since 4.0.0
	 */
	protected function writeSecret(
		string $key,
		#[SensitiveParameter]
		mixed $secret
	): bool {
		$secrets = $this->readSecrets();

		if ($secret === null) {
			unset($secrets[$key]);
		} else {
			$secrets[$key] = $secret;
		}

		// first line is always the password
		$lines = $secrets['password'] ?? '';

		// everything else is for the second line
		$secondLine = Json::encode(
			A::without($secrets, 'password')
		);

		if ($secondLine !== '[]') {
			$lines .= "\n" . $secondLine;
		}

		return F::write($this->secretsFile(), $lines);
	}
}
