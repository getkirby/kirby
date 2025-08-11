<?php

namespace Kirby\Permissions;

use Kirby\Toolkit\Reflection;

class Permissions
{
	public function __construct(
		public AccountPermissions $account = new AccountPermissions(),
		public FilePermissions $file = new FilePermissions(),
		public FilesPermissions $files = new FilesPermissions(),
		public LanguagePermissions $language = new LanguagePermissions(),
		public LanguagesPermissions $languages = new LanguagesPermissions(),
		public PagePermissions $page = new PagePermissions(),
		public PagesPermissions $pages = new PagesPermissions(),
		public PanelPermissions $panel = new PanelPermissions(),
		public SitePermissions $site = new SitePermissions(),
		public SystemPermissions $system = new SystemPermissions(),
		public UserPermissions $user = new UserPermissions(),
		public UsersPermissions $users = new UsersPermissions(),
	) {
	}

	public function __call(string $permissions, array $arguments = []): mixed
	{
		return $this->$permissions;
	}

	public static function forAdmin(): static
	{
		return static::fromWildcard(true);
	}

	public static function forNobody(): static
	{
		return static::fromWildcard(false);
	}

	public static function from(array|bool $permissions, string $role = '*'): static
	{
		if (is_bool($permissions) === true) {
			return static::fromWildcard($permissions);
		}

		if (isset($permissions['*']) === true) {
			$instance = static::fromWildcard($permissions['*']);
		} else {
			$instance = new static();
		}

		$props = Reflection::extractProps($permissions, static::class);

		foreach ($props as $key => $value) {
			$class = __NAMESPACE__ . '\\' . ucfirst($key) . 'Permissions';
			$instance->$key = $class::from($value, $role);
		}

		return $instance;
	}

	public static function fromWildcard(bool $wildcard): static
	{
		$instance = new static();

		foreach (Reflection::paramsNames(static::class) as $key) {
			$class = __NAMESPACE__ . '\\' . ucfirst($key) . 'Permissions';
			$instance->$key = $class::fromWildcard($wildcard);
		}

		return $instance;
	}

	public function toArray(): array
	{
		$array = [];

		foreach (Reflection::paramsNames(static::class) as $key) {
			$array[$key] = $this->$key->toArray();
		}

		return $array;
	}
}
