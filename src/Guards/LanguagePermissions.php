<?php

namespace Kirby\Guards;

use Kirby\Cms\Language;
use Kirby\Cms\Model;

/**
 * Role and blueprint based permissions for a `$language` object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguagePermissions extends ModelPermissions
{
	/**
	 * @var Language
	 */
	protected Model $model;

	public function category(): string
	{
		return 'languages';
	}

	public function error(string $key, array $data = []): never
	{
		parent::error(
			key: 'language.' . $key . '.permission',
			data: $data
		);
	}
}
