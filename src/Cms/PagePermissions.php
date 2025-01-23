<?php

namespace Kirby\Cms;

/**
 * PagePermissions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PagePermissions extends ModelPermissions
{
	protected const CATEGORY = 'pages';

	/**
	 * Used to cache once determined permissions in memory
	 */
	protected static function cacheKey(ModelWithContent|Language $model): string
	{
		return $model->intendedTemplate()->name();
	}

	protected function canChangeSlug(): bool
	{
		return $this->model->isHomeOrErrorPage() !== true;
	}

	protected function canChangeStatus(): bool
	{
		return $this->model->isErrorPage() !== true;
	}

	protected function canChangeTemplate(): bool
	{
		if ($this->model->isErrorPage() === true) {
			return false;
		}

		if (count($this->model->blueprints()) <= 1) {
			return false;
		}

		return true;
	}

	protected function canDelete(): bool
	{
		return $this->model->isHomeOrErrorPage() !== true;
	}

	protected function canMove(): bool
	{
		return $this->model->isHomeOrErrorPage() !== true;
	}

	protected function canSort(): bool
	{
		if ($this->model->isErrorPage() === true) {
			return false;
		}

		if ($this->model->isListed() !== true) {
			return false;
		}

		if ($this->model->blueprint()->num() !== 'default') {
			return false;
		}

		return true;
	}
}
