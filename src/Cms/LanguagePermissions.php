<?php

namespace Kirby\Cms;

/**
 * LanguagePermissions
 *
 * @package   Kirby Cms
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LanguagePermissions extends NewPermissions
{
	protected string   $category = 'languages';
	protected Language $model;

	public function __construct(Language $model)
	{
		$this->model       = $model;
		$this->user        = $model->kirby()->user() ?? User::nobody();
		$this->permissions = $this->user->role()->permissions();
	}

	protected function canDelete(): bool
	{
		return $this->model->isDeletable() === true;
	}

	public function toArray(): array
	{
		return [];
	}
}
