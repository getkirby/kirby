<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
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
	use HasModels;
	/**
	 * @use \Kirby\Cms\HasSiblings<\Kirby\Cms\Users>
	 */
	use HasSiblings;
	use UserActions;

	public const CLASS_ALIAS = 'user';

	/**
	 * All registered user methods
	 * @todo Remove when support for PHP 8.2 is dropped
	 */
	public static array $methods = [];

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
		$set = static function (string $key, Closure $callback) use ($props) {
			if ($value = $props[$key] ?? null) {
				$value = $callback($value);
			}

			return $value;
		};

		// if no ID passed, generate one;
		// do so before calling parent constructor
		// so it also gets stored in propertyData prop
		$props['id'] ??= $this->createId();

		$this->id       = $props['id'];
		$this->email    = $set('email', fn ($email) => Str::lower(trim($email)));
		$this->language = $set('language', fn ($language) => trim($language));
		$this->name     = $set('name', fn ($name) => trim(strip_tags($name)));
		$this->password = $props['password'] ?? null;
		$this->role     = $set('role', fn ($role) => Str::lower(trim($role)));

		// Set blueprint before setting content
		// or translations in the parent constructor.
		// Otherwise, the blueprint definition cannot be
		// used when creating the right field values
		// for the content.
		$this->setBlueprint($props['blueprint'] ?? null);

		parent::__construct($props);

		$this->setFiles($props['files'] ?? null);
	}

	/**
	 * Modified getter to also return fields
	 * from the content
	 */
	public function __call(string $method, array $arguments = []): mixed
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
	 */
	public function __debugInfo(): array
	{
		return [
			...$this->toArray(),
			'avatar'  => $this->avatar(),
			'content' => $this->content(),
			'role'    => $this->role()
		];
	}

	/**
	 * Returns the url to the api endpoint
	 * @internal
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
	 */
	public function avatar(): File|null
	{
		return $this->files()->template('avatar')->first();
	}

	/**
	 * Returns the UserBlueprint object
	 */
	public function blueprint(): UserBlueprint
	{
		try {
			return $this->blueprint ??= UserBlueprint::factory(
				'users/' . $this->role(),
				'users/default',
				$this
			);
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
	 * @internal
	 *
	 * @param string|null $languageCode Not used so far
	 */
	public function contentFileData(
		array $data,
		string|null $languageCode = null
	): array {
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

	protected function credentials(): array
	{
		return $this->credentials ??= $this->readCredentials();
	}

	/**
	 * Returns the user email address
	 */
	public function email(): string|null
	{
		return $this->email ??= $this->credentials()['email'] ?? null;
	}

	/**
	 * Checks if the user exists
	 */
	public function exists(): bool
	{
		return $this->version('latest')->exists('default');
	}

	/**
	 * Constructs a User object and also
	 * takes User models into account.
	 * @internal
	 */
	public static function factory(mixed $props): static
	{
		return static::model($props['model'] ?? $props['role'] ?? 'default', $props);
	}

	/**
	 * Hashes the user's password unless it is `null`,
	 * which will leave it as `null`
	 * @internal
	 */
	public static function hashPassword(
		#[SensitiveParameter]
		string|null $password = null
	): string|null {
		if ($password !== null) {
			$password = password_hash($password, PASSWORD_DEFAULT);
		}

		return $password;
	}

	/**
	 * Returns the user id
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * Returns the inventory of files
	 * children and content files
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
	 */
	public function is(User|null $user = null): bool
	{
		if ($user === null) {
			return false;
		}

		return $this->id() === $user->id();
	}

	/**
	 * Checks if this user has the admin role
	 */
	public function isAdmin(): bool
	{
		return $this->role()->id() === 'admin';
	}

	/**
	 * Checks if the current user is the virtual
	 * Kirby user
	 */
	public function isKirby(): bool
	{
		return $this->isAdmin() && $this->id() === 'kirby';
	}

	/**
	 * Checks if the current user is this user
	 */
	public function isLoggedIn(): bool
	{
		return $this->is($this->kirby()->user());
	}

	/**
	 * Checks if the user is the last one
	 * with the admin role
	 */
	public function isLastAdmin(): bool
	{
		return
			$this->role()->isAdmin() === true &&
			$this->kirby()->users()->filter('role', 'admin')->count() <= 1;
	}

	/**
	 * Checks if the user is the last user
	 */
	public function isLastUser(): bool
	{
		return $this->kirby()->users()->count() === 1;
	}

	/**
	 * Checks if the current user is the virtual
	 * Nobody user
	 */
	public function isNobody(): bool
	{
		return $this->role()->id() === 'nobody' && $this->id() === 'nobody';
	}

	/**
	 * Returns the user language
	 */
	public function language(): string
	{
		return $this->language ??=
			$this->credentials()['language'] ??
			$this->kirby()->panelLanguage();
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
	 */
	public function loginPasswordless(
		Session|array|null $session = null
	): void {
		if ($this->id() === 'kirby') {
			throw new PermissionException(
				message: 'The almighty user "kirby" cannot be used for login, only for raising permissions in code via `$kirby->impersonate()`'
			);
		}

		$kirby   = $this->kirby();
		$session = $this->sessionFromOptions($session);

		$kirby->trigger(
			'user.login:before',
			['user' => $this, 'session' => $session]
		);

		$session->regenerateToken(); // privilege change
		$session->data()->set('kirby.userId', $this->id());

		if ($this->passwordTimestamp() !== null) {
			$session->data()->set('kirby.loginTimestamp', time());
		}

		$kirby->auth()->setUser($this);

		$kirby->trigger(
			'user.login:after',
			['user' => $this, 'session' => $session]
		);
	}

	/**
	 * Logs the user out
	 *
	 * @param \Kirby\Session\Session|array|null $session Session options or session object to unset the user in
	 */
	public function logout(Session|array|null $session = null): void
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
	 * Returns the absolute path to the media folder for the user
	 * @internal
	 */
	public function mediaDir(): string
	{
		return $this->kirby()->root('media') . '/users/' . $this->id();
	}

	/**
	 * @see `::mediaDir`
	 * @internal
	 */
	public function mediaRoot(): string
	{
		return $this->mediaDir();
	}

	/**
	 * Returns the media url for the user object
	 * @internal
	 */
	public function mediaUrl(): string
	{
		return $this->kirby()->url('media') . '/users/' . $this->id();
	}

	/**
	 * Returns the last modification date of the user
	 */
	public function modified(
		string $format = 'U',
		string|null $handler = null,
		string|null $languageCode = null
	): int|string|false {
		$modifiedContent = $this->version('latest')->modified($languageCode ?? 'current');
		$modifiedIndex   = F::modified($this->root() . '/index.php');
		$modifiedTotal   = max([$modifiedContent, $modifiedIndex]);

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
	 */
	public function nameOrEmail(): Field
	{
		return $this->name()->or(new Field($this, 'email', $this->email()));
	}

	/**
	 * Create a dummy nobody
	 * @internal
	 */
	public static function nobody(): static
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
		$file = $this->secretsFile();

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

		$name = $this->role ?? $this->credentials()['role'] ?? 'default';

		return $this->role =
			$this->kirby()->roles()->find($name) ??
			Role::defaultNobody();
	}

	/**
	 * Returns all available roles for this user,
	 * that the authenticated user can change to.
	 *
	 * For all roles the current user can create
	 * use `$kirby->roles()->canBeCreated()`.
	 */
	public function roles(): Roles
	{
		$kirby = $this->kirby();
		$roles = $kirby->roles();

		// if the authenticated user doesn't have the permission to change
		// the role of this user, only the current role is available
		if ($this->permissions()->can('changeRole') === false) {
			return $roles->filter('id', $this->role()->id());
		}

		return $roles->canBeCreated();
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
	 * Reads a specific secret from the user secrets file on disk
	 * @since 4.0.0
	 */
	public function secret(string $key): mixed
	{
		return $this->readSecrets()[$key] ?? null;
	}

	/**
	 * Sets the Blueprint object
	 *
	 * @return $this
	 */
	protected function setBlueprint(array|null $blueprint = null): static
	{
		if ($blueprint !== null) {
			$this->blueprint = new UserBlueprint([
				...$blueprint,
				'model' => $this
			]);
		}

		return $this;
	}

	/**
	 * Converts session options into a session object
	 *
	 * @param \Kirby\Session\Session|array $session Session options or session object to unset the user in
	 */
	protected function sessionFromOptions(Session|array|null $session): Session
	{
		// use passed session options or session object if set
		$session ??= ['detect' => true];

		if ($session instanceof Session === false) {
			$session = $this->kirby()->session($session);
		}

		return $session;
	}

	/**
	 * Returns the parent Users collection
	 */
	protected function siblingsCollection(): Users
	{
		return $this->kirby()->users()->sortBy('username', 'asc');
	}

	/**
	 * Converts the most important user properties
	 * to an array
	 */
	public function toArray(): array
	{
		return [
			...parent::toArray(),
			'avatar'   => $this->avatar()?->toArray(),
			'email'    => $this->email(),
			'id'       => $this->id(),
			'language' => $this->language(),
			'role'     => $this->role()->name(),
			'username' => $this->username()
		];
	}

	/**
	 * String template builder
	 *
	 * @param string|null $fallback Fallback for tokens in the template that cannot be replaced
	 *                              (`null` to keep the original token)
	 */
	public function toString(
		string|null $template = null,
		array $data = [],
		string|null $fallback = '',
		string $handler = 'template'
	): string {
		return parent::toString(
			$template ?? $this->email(),
			$data,
			$fallback,
			$handler
		);
	}

	/**
	 * Returns the username
	 * which is the given name or the email
	 * as a fallback
	 */
	public function username(): string|null
	{
		return $this->nameOrEmail()->value();
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
		string|null $password = null
	): bool {
		if (empty($this->password()) === true) {
			throw new NotFoundException(
				key: 'user.password.undefined'
			);
		}

		// `UserRules` enforces a minimum length of 8 characters,
		// so everything below that is a typo
		if (Str::length($password) < 8) {
			throw new InvalidArgumentException(
				key: 'user.password.invalid'
			);
		}

		// too long passwords can cause DoS attacks
		if (Str::length($password) > 1000) {
			throw new InvalidArgumentException(
				key: 'user.password.excessive'
			);
		}

		if (password_verify($password, $this->password()) !== true) {
			throw new InvalidArgumentException(
				key: 'user.password.wrong',
				httpCode: 401
			);
		}

		return true;
	}

	/**
	 * Returns the path to the file containing
	 * all user secrets, including the password
	 * @since 4.0.0
	 */
	protected function secretsFile(): string
	{
		return $this->root() . '/.htpasswd';
	}
}
