<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;

/**
 * Role and blueprint based permissions for a `$language` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelPermissions<Language>
 */
class LanguagePermissions extends ModelPermissions
{
	public function category(): string
	{
		return 'languages';
	}

	protected function error(string $key, array $data = []): never
	{
		parent::error(
			key: 'language.' . $key . '.permission',
			data: $data
		);
	}
}
