<?php

namespace Kirby\Permissions;

use Kirby\Reflection\Constructor;

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

		$props = (new Constructor(static::class))->getAcceptedArguments($permissions);

		foreach ($props as $key => $value) {
			$class = __NAMESPACE__ . '\\' . ucfirst($key) . 'Permissions';
			$instance->$key = $class::from($value, $role);
		}

		return $instance;
	}

	public static function fromWildcard(bool $wildcard): static
	{
		$instance = new static();

		foreach (static::keys() as $key) {
			$class = __NAMESPACE__ . '\\' . ucfirst($key) . 'Permissions';
			$instance->$key = $class::fromWildcard($wildcard);
		}

		return $instance;
	}

	public static function keys(): array
	{
		return (new Constructor(static::class))->getParameterNames();
	}

	public function toArray(): array
	{
		$array = [];

		foreach (static::keys() as $key) {
			$array[$key] = $this->$key->toArray();
		}

		return $array;
	}
}
