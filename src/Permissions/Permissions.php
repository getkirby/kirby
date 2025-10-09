<?php

namespace Kirby\Permissions;

use Kirby\Reflection\Constructor;

class Permissions extends Foundation
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

	public static function fromArgs(array $args, string $role = '*'): static
	{
		if (isset($args['*']) === true) {
			$instance = static::fromWildcard($args['*']);
		} else {
			$instance = new static();
		}

		$args = (new Constructor(static::class))->getAcceptedArguments($args);

		foreach ($args as $key => $value) {
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
}
