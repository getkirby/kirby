<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Panel\User as Panel;
use Kirby\Session\Session;
use Kirby\Toolkit\Str;
use SensitiveParameter;

/**
 * The `$user` object represents a
 * single Panel or frontend user.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class User extends ModelWithContent
{
	use HasFiles;
	use HasMethods;
	use HasSiblings;
	use UserActions;

	public const CLASS_ALIAS = 'user';

	/**
	 * All registered user methods
	 */
	public static array $methods = [];

	/**
	 * Registry with all User models
	 */
	public static array $models = [];

	protected UserBlueprint|null $blueprint = null;
	protected array $credentials;
	protected string|null $email;
	protected string $hash;
	protected string $id;
	protected array|null $inventory = null;
	protected string|null $language;
	protected Field|string|null $name;
	protected string|null $password;
	protected Role|string|null $role;

	/**
	 * Creates a new User object
	 */
	public function __construct(array $props)
	{
		// helper function to easily edit values (if not null)
		// before assigning them to their properties
		$set = function (string $key, Closure $callback) use ($props) {
			if ($value = $props[$key] ?? null) {
				$value = $callback($value);
			}

			return $value;
		};

		// if no ID passed, generate one;
		// do so before calling parent constructor
		// so it also gets stored in propertyData prop
		$props['id'] ??= $this->createId();

		parent::__construct($props);

		$this->id       = $props['id'];
		$this->email    = $set('email', fn ($email) => Str::lower(trim($email)));
		$this->language = $set('language', fn ($language) => trim($language));
		$this->name     = $set('name', fn ($name) => trim(strip_tags($name)));
		$this->password = $props['password'] ?? null;
		$this->role     = $set('role', fn ($role) => Str::lower(trim($role)));

		$this->setBlueprint($props['blueprint'] ?? null);
		$this->setFiles($props['files'] ?? null);
	}

	/**
	 * Modified getter to also return fields
	 * from the content
	 *
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call(string $method, array $arguments = [])
	{
		// public property access
		if (isset($this->$method) === true) {
			return $this->$method;
		}

		// user methods
		if ($this->hasMethod($method)) {
			return $this->callMethod($method, $arguments);
		}

		// return site content otherwise
		return $this->content()->get($method);
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function __debugInfo(): array
	{
		return array_merge($this->toArray(), [
			'avatar'  => $this->avatar(),
			'content' => $this->content(),
			'role'    => $this->role()
		]);
	}

	/**
	 * Returns the url to the api endpoint
	 *
	 * @internal
	 * @param bool $relative
	 * @return string
	 */
	public function apiUrl(bool $relative = false): string
	{
		if ($relative === true) {
			return 'users/' . $this->id();
		}

		return $this->kirby()->url('api') . '/users/' . $this->id();
	}

	/**
	 * Returns the File object for the avatar or null
	 *
	 * @return \Kirby\Cms\File|null
	 */
	public function avatar()
	{
		return $this->files()->template('avatar')->first();
	}

	/**
	 * Returns the UserBlueprint object
	 */
	public function blueprint(): UserBlueprint
	{
		try {
			return $this->blueprint ??= UserBlueprint::factory('users/' . $this->role(), 'users/default', $this);
		} catch (Exception) {
			return $this->blueprint ??= new UserBlueprint([
				'model' => $this,
				'name'  => 'default',
				'title' => 'Default',
			]);
		}
	}

	/**
	 * Prepares the content for the write method
	 *
	 * @internal
	 * @param array $data
	 * @param string $languageCode|null Not used so far
	 * @return array
	 */
	public function contentFileData(array $data, string $languageCode = null): array
	{
		// remove stuff that has nothing to do in the text files
		unset(
			$data['email'],
			$data['language'],
			$data['name'],
			$data['password'],
			$data['role']
		);

		return $data;
	}

	/**
	 * Filename for the content file
	 *
	 * @internal
	 * @deprecated 4.0.0
	 * @todo Remove in v5
	 * @codeCoverageIgnore
	 */
	public function contentFileName(): string
	{
		Helpers::deprecated('The internal $model->contentFileName() method has been deprecated. Please let us know via a GitHub issue if you need this method and tell us your use case.', 'model-content-file');
		return 'user';
	}

	protected function credentials(): array
	{
		return $this->credentials ??= $this->readCredentials();
	}

	/**
	 * Returns the user email address
	 *
	 * @return string
	 */
	public function email(): string|null
	{
		return $this->email ??= $this->credentials()['email'] ?? null;
	}

	/**
	 * Checks if the user exists
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return $this->storage()->exists(
			'published',
			'default'
		);
	}

	/**
	 * Constructs a User object and also
	 * takes User models into account.
	 *
	 * @internal
	 * @param mixed $props
	 * @return static
	 */
	public static function factory($props)
	{
		if (empty($props['model']) === false) {
			return static::model($props['model'], $props);
		}

		return new static($props);
	}

	/**
	 * Hashes the user's password unless it is `null`,
	 * which will leave it as `null`
	 *
	 * @internal
	 */
	public static function hashPassword(
		#[SensitiveParameter]
		string $password = null
	): string|null {
		if ($password !== null) {
			$password = password_hash($password, PASSWORD_DEFAULT);
		}

		return $password;
	}

	/**
	 * Returns the user id
	 *
	 * @return string
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * Returns the inventory of files
	 * children and content files
	 *
	 * @return array
	 */
	public function inventory(): array
	{
		if ($this->inventory !== null) {
			return $this->inventory;
		}

		$kirby = $this->kirby();

		return $this->inventory = Dir::inventory(
			$this->root(),
			$kirby->contentExtension(),
			$kirby->contentIgnore(),
			$kirby->multilang()
		);
	}

	/**
	 * Compares the current object with the given user object
	 *
	 * @param \Kirby\Cms\User|null $user
	 * @return bool
	 */
	public function is(User $user = null): bool
	{
		if ($user === null) {
			return false;
		}

		return $this->id() === $user->id();
	}

	/**
	 * Checks if this user has the admin role
	 *
	 * @return bool
	 */
	public function isAdmin(): bool
	{
		return $this->role()->id() === 'admin';
	}

	/**
	 * Checks if the current user is the virtual
	 * Kirby user
	 *
	 * @return bool
	 */
	public function isKirby(): bool
	{
		return $this->email() === 'kirby@getkirby.com';
	}

	/**
	 * Checks if the current user is this user
	 *
	 * @return bool
	 */
	public function isLoggedIn(): bool
	{
		return $this->is($this->kirby()->user());
	}

	/**
	 * Checks if the user is the last one
	 * with the admin role
	 *
	 * @return bool
	 */
	public function isLastAdmin(): bool
	{
		return
			$this->role()->isAdmin() === true &&
			$this->kirby()->users()->filter('role', 'admin')->count() <= 1;
	}

	/**
	 * Checks if the user is the last user
	 *
	 * @return bool
	 */
	public function isLastUser(): bool
	{
		return $this->kirby()->users()->count() === 1;
	}

	/**
	 * Checks if the current user is the virtual
	 * Nobody user
	 *
	 * @return bool
	 */
	public function isNobody(): bool
	{
		return $this->email() === 'nobody@getkirby.com';
	}

	/**
	 * Returns the user language
	 *
	 * @return string
	 */
	public function language(): string
	{
		return $this->language ??= $this->credentials()['language'] ?? $this->kirby()->panelLanguage();
	}

	/**
	 * Logs the user in
	 *
	 * @param \Kirby\Session\Session|array|null $session Session options or session object to set the user in
	 */
	public function login(
		#[SensitiveParameter]
		string $password,
		$session = null
	): bool {
		$this->validatePassword($password);
		$this->loginPasswordless($session);

		return true;
	}

	/**
	 * Logs the user in without checking the password
	 *
	 * @param \Kirby\Session\Session|array|null $session Session options or session object to set the user in
	 * @return void
	 */
	public function loginPasswordless($session = null): void
	{
		$kirby = $this->kirby();

		$session = $this->sessionFromOptions($session);

		$kirby->trigger('user.login:before', ['user' => $this, 'session' => $session]);

		$session->regenerateToken(); // privilege change
		$session->data()->set('kirby.userId', $this->id());
		if ($this->passwordTimestamp() !== null) {
			$session->data()->set('kirby.loginTimestamp', time());
		}
		$this->kirby()->auth()->setUser($this);

		$kirby->trigger('user.login:after', ['user' => $this, 'session' => $session]);
	}

	/**
	 * Logs the user out
	 *
	 * @param \Kirby\Session\Session|array|null $session Session options or session object to unset the user in
	 * @return void
	 */
	public function logout($session = null): void
	{
		$kirby   = $this->kirby();
		$session = $this->sessionFromOptions($session);

		$kirby->trigger('user.logout:before', ['user' => $this, 'session' => $session]);

		// remove the user from the session for future requests
		$session->data()->remove('kirby.userId');
		$session->data()->remove('kirby.loginTimestamp');

		// clear the cached user object from the app state of the current request
		$this->kirby()->auth()->flush();

		if ($session->data()->get() === []) {
			// session is now empty, we might as well destroy it
			$session->destroy();

			$kirby->trigger('user.logout:after', ['user' => $this, 'session' => null]);
		} else {
			// privilege change
			$session->regenerateToken();

			$kirby->trigger('user.logout:after', ['user' => $this, 'session' => $session]);
		}
	}

	/**
	 * Returns the root to the media folder for the user
	 *
	 * @internal
	 * @return string
	 */
	public function mediaRoot(): string
	{
		return $this->kirby()->root('media') . '/users/' . $this->id();
	}

	/**
	 * Returns the media url for the user object
	 *
	 * @internal
	 * @return string
	 */
	public function mediaUrl(): string
	{
		return $this->kirby()->url('media') . '/users/' . $this->id();
	}

	/**
	 * Creates a user model if it has been registered
	 *
	 * @internal
	 * @param string $name
	 * @param array $props
	 * @return \Kirby\Cms\User
	 */
	public static function model(string $name, array $props = [])
	{
		if ($class = (static::$models[$name] ?? null)) {
			$object = new $class($props);

			if ($object instanceof self) {
				return $object;
			}
		}

		return new static($props);
	}

	/**
	 * Returns the last modification date of the user
	 *
	 * @param string $format
	 * @param string|null $handler
	 * @param string|null $languageCode
	 * @return int|string
	 */
	public function modified(string $format = 'U', string $handler = null, string $languageCode = null)
	{
		$modifiedContent = $this->storage()->modified('published', $languageCode);
		$modifiedIndex   = F::modified($this->root() . '/index.php');
		$modifiedTotal   = max([$modifiedContent, $modifiedIndex]);
		$handler       ??= $this->kirby()->option('date.handler', 'date');

		return Str::date($modifiedTotal, $format, $handler);
	}

	/**
	 * Returns the user's name
	 */
	public function name(): Field
	{
		if (is_string($this->name) === true) {
			return new Field($this, 'name', $this->name);
		}

		return $this->name ??= new Field($this, 'name', $this->credentials()['name'] ?? null);
	}

	/**
	 * Returns the user's name or,
	 * if empty, the email address
	 *
	 * @return \Kirby\Content\Field
	 */
	public function nameOrEmail()
	{
		$name = $this->name();
		return $name->isNotEmpty() ? $name : new Field($this, 'email', $this->email());
	}

	/**
	 * Create a dummy nobody
	 *
	 * @internal
	 * @return static
	 */
	public static function nobody()
	{
		return new static([
			'email' => 'nobody@getkirby.com',
			'role'  => 'nobody'
		]);
	}

	/**
	 * Returns the panel info object
	 */
	public function panel(): Panel
	{
		return new Panel($this);
	}

	/**
	 * Returns the encrypted user password
	 */
	public function password(): string|null
	{
		return $this->password ??= $this->readPassword();
	}

	/**
	 * Returns the timestamp when the password
	 * was last changed
	 */
	public function passwordTimestamp(): int|null
	{
		$file = $this->passwordFile();

		// ensure we have the latest information
		// to prevent cache attacks
		clearstatcache();

		// user does not have a password
		if (is_file($file) === false) {
			return null;
		}

		return filemtime($file);
	}

	public function permissions(): UserPermissions
	{
		return new UserPermissions($this);
	}

	/**
	 * Returns the user role
	 */
	public function role(): Role
	{
		if ($this->role instanceof Role) {
			return $this->role;
		}

		$name = $this->role ?? $this->credentials()['role'] ?? 'visitor';

		return $this->role = $this->kirby()->roles()->find($name) ?? Role::nobody();
	}

	/**
	 * Returns all available roles
	 * for this user, that can be selected
	 * by the authenticated user
	 *
	 * @return \Kirby\Cms\Roles
	 */
	public function roles()
	{
		$kirby = $this->kirby();
		$roles = $kirby->roles();

		// a collection with just the one role of the user
		$myRole = $roles->filter('id', $this->role()->id());

		// if there's an authenticated user …
		// admin users can select pretty much any role
		if ($kirby->user()?->isAdmin() === true) {
			// except if the user is the last admin
			if ($this->isLastAdmin() === true) {
				// in which case they have to stay admin
				return $myRole;
			}

			// return all roles for mighty admins
			return $roles;
		}

		// any other user can only keep their role
		return $myRole;
	}

	/**
	 * The absolute path to the user directory
	 */
	public function root(): string
	{
		return $this->kirby()->root('accounts') . '/' . $this->id();
	}

	/**
	 * Returns the UserRules class to
	 * validate any important action.
	 */
	protected function rules(): UserRules
	{
		return new UserRules();
	}

	/**
	 * Sets the Blueprint object
	 *
	 * @return $this
	 */
	protected function setBlueprint(array $blueprint = null): static
	{
		if ($blueprint !== null) {
			$blueprint['model'] = $this;
			$this->blueprint = new UserBlueprint($blueprint);
		}

		return $this;
	}

	/**
	 * Converts session options into a session object
	 *
	 * @param \Kirby\Session\Session|array $session Session options or session object to unset the user in
	 * @return \Kirby\Session\Session
	 */
	protected function sessionFromOptions($session)
	{
		// use passed session options or session object if set
		if (is_array($session) === true) {
			$session = $this->kirby()->session($session);
		} elseif ($session instanceof Session === false) {
			$session = $this->kirby()->session(['detect' => true]);
		}

		return $session;
	}

	/**
	 * Returns the parent Users collection
	 */
	protected function siblingsCollection(): Users
	{
		return $this->kirby()->users();
	}

	/**
	 * Converts the most important user properties
	 * to an array
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'avatar'   => $this->avatar()?->toArray(),
			'email'    => $this->email(),
			'id'       => $this->id(),
			'language' => $this->language(),
			'role'     => $this->role()->name(),
			'username' => $this->username()
		]);
	}

	/**
	 * String template builder
	 *
	 * @param string|null $fallback Fallback for tokens in the template that cannot be replaced
	 *                              (`null` to keep the original token)
	 */
	public function toString(
		string $template = null,
		array $data = [],
		string|null $fallback = '',
		string $handler = 'template'
	): string {
		$template ??= $this->email();
		return parent::toString($template, $data, $fallback, $handler);
	}

	/**
	 * Returns the username
	 * which is the given name or the email
	 * as a fallback
	 */
	public function username(): string|null
	{
		return $this->name()->or($this->email())->value();
	}

	/**
	 * Compares the given password with the stored one
	 *
	 * @throws \Kirby\Exception\NotFoundException If the user has no password
	 * @throws \Kirby\Exception\InvalidArgumentException If the entered password is not valid
	 *                                                   or does not match the user password
	 */
	public function validatePassword(
		#[SensitiveParameter]
		string $password = null
	): bool {
		if (empty($this->password()) === true) {
			throw new NotFoundException(['key' => 'user.password.undefined']);
		}

		// `UserRules` enforces a minimum length of 8 characters,
		// so everything below that is a typo
		if (Str::length($password) < 8) {
			throw new InvalidArgumentException(['key' => 'user.password.invalid']);
		}

		// too long passwords can cause DoS attacks
		if (Str::length($password) > 1000) {
			throw new InvalidArgumentException(['key' => 'user.password.excessive']);
		}

		if (password_verify($password, $this->password()) !== true) {
			throw new InvalidArgumentException(['key' => 'user.password.wrong', 'httpCode' => 401]);
		}

		return true;
	}

	/**
	 * Returns the path to the password file
	 */
	protected function passwordFile(): string
	{
		return $this->root() . '/.htpasswd';
	}
}
