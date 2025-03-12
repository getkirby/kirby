<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\MemoryStorage;
use Kirby\Data\Data;
use Kirby\Data\Json;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
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

			return $user;
		});
	}

	/**
	 * Changes the user password
	 *
	 * If this method is used with user input, it is recommended to also
	 * confirm the current password by the user via `::validatePassword()`
	 */
	public function changePassword(
		#[SensitiveParameter]
		string $password
	): static {
		return $this->commit('changePassword', ['user' => $this, 'password' => $password], function ($user, $password) {
			$user = $user->clone([
				'password' => $password = static::hashPassword($password)
			]);

			$user->writePassword($password);

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

		$commit = new ModelCommit(
			model: $this,
			action: $action
		);

		return $commit->call($arguments, $callback);
	}

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
			// delete all files individually
			foreach ($user->files() as $file) {
				$file->delete();
			}

			// delete all versions,
			// the plain text storage handler will then clean
			// up the directory if it's empty
			$user->versions()->delete();

			// delete the user directory to get rid
			// of the .htpasswd and index.php files.
			// we need to solve this at a later point with
			// something like a credential storage
			Dir::remove($user->root());

			return true;
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

			ModelState::update(
				method: 'set',
				current: $user,
			);
		}

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
