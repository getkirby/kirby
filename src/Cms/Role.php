<?php

namespace Kirby\Cms;

use Kirby\Blueprint\Blueprint;
use Kirby\Data\Data;
use Kirby\Filesystem\F;
use Kirby\Toolkit\BlockCollectionAccess;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Stringable;

/**
 * Represents a User role with attached
 * permissions. Roles are defined by user blueprints.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Role implements Stringable
{
	protected string|null $description;
	protected string $name;
	protected Permissions $permissions;
	protected string|null $title;

	/**
	 * @param array<string, mixed> $props
	 */
	public function __construct(array $props)
	{
		$this->name        = $props['name'];
		$this->permissions = new Permissions($props['permissions'] ?? null);
		$title             = $props['title'] ?? null;
		$this->title       = I18n::translate($title) ?? $title;
		$description       = $props['description'] ?? null;
		$this->description = I18n::translate($description) ?? $description;
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns the role name
	 * when the role is converted to a string
	 */
	public function __toString(): string
	{
		return $this->name();
	}

	/**
	 * Creates the built-in admin role
	 *
	 * @param array<string, mixed> $inject Props to add if not defined
	 */
	public static function defaultAdmin(array $inject = []): static
	{
		return static::factory(static::defaults()['admin'], $inject);
	}

	/**
	 * Creates the built-in nobody role
	 *
	 * @param array<string, mixed> $inject Props to add if not defined
	 */
	public static function defaultNobody(array $inject = []): static
	{
		return static::factory(static::defaults()['nobody'], $inject);
	}

	/**
	 * @return array{admin: array<string, mixed>, nobody: array<string, mixed>}
	 */
	protected static function defaults(): array
	{
		return [
			'admin' => [
				'name'        => 'admin',
				'description' => I18n::translate('role.admin.description'),
				'title'       => I18n::translate('role.admin.title'),
				'permissions' => true,
			],
			'nobody' => [
				'name'        => 'nobody',
				'description' => I18n::translate('role.nobody.description'),
				'title'       => I18n::translate('role.nobody.title'),
				'permissions' => false,
			]
		];
	}

	/**
	 * Returns the translated description of the role
	 */
	public function description(): string|null
	{
		return $this->description;
	}

	/**
	 * Creates a role from the given props
	 * and resolves blueprint extensions
	 *
	 * @param array<string, mixed> $props
	 * @param array<string, mixed> $inject Props to add if not defined
	 */
	public static function factory(array $props, array $inject = []): static
	{
		// ensure to properly extend the blueprint
		$props = Blueprint::extend($props + $inject);

		return new static($props);
	}

	/**
	 * Returns the role name as id
	 */
	public function id(): string
	{
		return $this->name();
	}

	/**
	 * Compares the current object with the given role object
	 * @since 5.4.0
	 */
	public function is(Role|null $role = null): bool
	{
		if ($role === null) {
			return false;
		}

		return $this->id() === $role->id();
	}

	/**
	 * Checks if the role is accessible to the current user
	 * @since 5.4.0
	 */
	public function isAccessible(): bool
	{
		$user = App::instance()->user();

		// no access without authenticated user
		if ($user === null) {
			return false;
		}

		// check `user.access` for the current user with the same role
		// (also ensures `access` option of the user's current role)
		if ($user->role()->is($this) === true) {
			return $user->isAccessible();
		}

		// check `users.access` for different roles
		// (also ensures `access` option of the target role)
		$tmpUser = new User([
			'email' => 'test@getkirby.com',
			'role'  => $this->id()
		]);

		return $tmpUser->isAccessible();
	}

	/**
	 * Checks if this is the admin role
	 */
	public function isAdmin(): bool
	{
		return $this->name() === 'admin';
	}

	/**
	 * Checks if this is the nobody role
	 */
	public function isNobody(): bool
	{
		return $this->name() === 'nobody';
	}

	/**
	 * Creates a role from the given blueprint file
	 *
	 * @param array<string, mixed> $inject Props to add if not defined
	 */
	#[BlockCollectionAccess]
	public static function load(string $file, array $inject = []): static
	{
		$data = [
			...Data::read($file),
			'name' => F::name($file)
		];

		return static::factory($data, $inject);
	}

	/**
	 * Returns the role name
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Returns the permissions object for this role
	 */
	public function permissions(): Permissions
	{
		return $this->permissions;
	}

	/**
	 * Returns the translated title of the role
	 * or the name as fallback
	 */
	public function title(): string
	{
		return $this->title ??= Str::label($this->name());
	}

	/**
	 * Converts the most important role
	 * properties to an array
	 *
	 * @return array{description: string|null, id: string, name: string, permissions: array, title: string}
	 */
	public function toArray(): array
	{
		return [
			'description' => $this->description(),
			'id'          => $this->id(),
			'name'        => $this->name(),
			'permissions' => $this->permissions()->toArray(),
			'title'       => $this->title(),
		];
	}
}
