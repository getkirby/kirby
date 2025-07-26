<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\PagePicker as CmsPagePicker;

/**
 * Page picker functionality
 *
 * @since 6.0.0
 */
trait PagePicker
{
	public function pagepicker(array $params = []): array
	{
		// inject the current model
		$params['model'] = $this->model();

		return (new CmsPagePicker($params))->toArray();
	}
}
