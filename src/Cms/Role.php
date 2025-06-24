<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Filesystem\F;
use Kirby\Toolkit\I18n;
use Stringable;

/**
 * Represents a User role with attached
 * permissions. Roles are defined by user blueprints.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Role implements Stringable
{
	protected string|null $description;
	protected string $name;
	protected Permissions $permissions;
	protected string|null $title;

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

	public function __toString(): string
	{
		return $this->name();
	}

	public static function defaultAdmin(array $inject = []): static
	{
		return static::factory(static::defaults()['admin'], $inject);
	}

	public static function defaultNobody(array $inject = []): static
	{
		return static::factory(static::defaults()['nobody'], $inject);
	}

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

	public function description(): string|null
	{
		return $this->description;
	}

	public static function factory(array $props, array $inject = []): static
	{
		// ensure to properly extend the blueprint
		$props = $props + $inject;
		$props = Blueprint::extend($props);

		return new static($props);
	}

	public function id(): string
	{
		return $this->name();
	}

	public function isAdmin(): bool
	{
		return $this->name() === 'admin';
	}

	public function isNobody(): bool
	{
		return $this->name() === 'nobody';
	}

	public static function load(string $file, array $inject = []): static
	{
		$data = [
			...Data::read($file),
			'name' => F::name($file)
		];

		return static::factory($data, $inject);
	}

	public function name(): string
	{
		return $this->name;
	}

	public function permissions(): Permissions
	{
		return $this->permissions;
	}

	public function title(): string
	{
		return $this->title ??= ucfirst($this->name());
	}

	/**
	 * Converts the most important role
	 * properties to an array
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
