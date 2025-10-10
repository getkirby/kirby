<?php

namespace Kirby\Permissions;

use Kirby\Permissions\Abstracts\PermissionsGroups;

class Permissions extends PermissionsGroups
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
}
