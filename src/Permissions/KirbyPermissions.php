<?php

namespace Kirby\Permissions;

/**
 * All Permissions in Kirby as a big group object
 */
class KirbyPermissions extends Permissions
{
	public function __construct(
		public AccountPermissions $account = new AccountPermissions(),
		public FilePermissions $file = new FilePermissions(),
		public LanguagePermissions $language = new LanguagePermissions(),
		public LanguagesPermissions $languages = new LanguagesPermissions(),
		public LanguageVariablePermissions $languageVariable = new LanguageVariablePermissions(),
		public LanguageVariablesPermissions $languageVariables = new LanguageVariablesPermissions(),
		public PagePermissions $page = new PagePermissions(),
		public PanelPermissions $panel = new PanelPermissions(),
		public SitePermissions $site = new SitePermissions(),
		public SystemPermissions $system = new SystemPermissions(),
		public UserPermissions $user = new UserPermissions(),
		public UsersPermissions $users = new UsersPermissions(),
	) {
	}

	/**
	 * Creates a permission object form array, boolean or null. This is mainly
	 * intended as a translator for the permissions list in a User blueprint.
	 */
	public static function from(array|bool|null $permissions): static
	{
		$instance = new static();

		if ($permissions === true || $permissions === false || $permissions === null) {
			return $instance->wildcard($permissions);
		}

		if (isset($permissions['*']) === true) {
			$instance->wildcard($permissions['*']);
		}

		foreach (static::acceptedArguments($permissions) as $key => $value) {
			$instance->$key = $instance->$key::from($value);
		}

		return $instance;
	}

	/**
	 * Converts the instance and all nested permission groups into a
	 * a multi-dimensional array. Mostly useful for debugging reasons.
	 */
	public function toArray(): array
	{
		$props = [];

		foreach (static::keys() as $param) {
			$props[$param] = $this->$param->toArray();
		}

		return $props;
	}

	/**
	 * Sets all permissions to either `true`, `false` or `null`
	 * as a starting point for more fine-grained permission definitions
	 * or as a wildcard option for admins or nobodies.
	 */
	public function wildcard(bool|null $wildcard): static
	{
		foreach (static::keys() as $key) {
			$this->$key = $this->$key->wildcard($wildcard);
		}

		return $this;
	}
}
