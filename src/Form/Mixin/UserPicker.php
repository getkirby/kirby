<?php


namespace Kirby\Form\Mixin;

use Kirby\Cms\UserPicker as CmsUserPicker;

/**
 * User picker functionality
 *
 * @since 6.0.0
 */
trait UserPicker
{
	public function userpicker(array $params = []): array
	{
		$params['model'] = $this->model();

		return (new CmsUserPicker($params))->toArray();
	}
}
