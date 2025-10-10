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
		public LanguageVariablePermissions $languageVariable = new LanguageVariablePermissions(),
		public LanguageVariablesPermissions $languageVariables = new LanguageVariablesPermissions(),
		public PagePermissions $page = new PagePermissions(),
		public PagesPermissions $pages = new PagesPermissions(),
		public PanelPermissions $panel = new PanelPermissions(),
		public SitePermissions $site = new SitePermissions(),
		public SystemPermissions $system = new SystemPermissions(),
		public UserPermissions $user = new UserPermissions(),
		public UsersPermissions $users = new UsersPermissions(),
	) {
	}

	public static function fromArray(array $args, string $role = '*'): static
	{
		if (isset($args['*']) === true) {
			$instance = static::fromWildcard($args['*']);
		} else {
			$instance = new static();
		}

		$args = (new Constructor(static::class))->getAcceptedArguments($args);

		foreach ($args as $key => $value) {
			$instance->$key = ($instance->$key)::from($value, $role);
		}

		return $instance;
	}

	public static function fromWildcard(bool $wildcard): static
	{
		$instance = new static();
		$args     = array_fill_keys(static::keys(), $wildcard);

		foreach ($args as $key => $value) {
			$instance->$key = ($instance->$key)::fromWildcard($value);
		}

		return $instance;
	}

	public function toArray(): array
	{
		$props = [];

		foreach (static::keys() as $param) {
			$props[$param] = $this->$param->toArray();
		}

		return $props;
	}
}
