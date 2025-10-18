<?php

namespace Kirby\Permissions;

class LanguageVariablesPermissions extends Permissions
{
	public function __construct(
		public bool|null $access = null,
	) {
	}
}
