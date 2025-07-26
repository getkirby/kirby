<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\FilePicker as CmsFilePicker;

/**
 * File picker functionality
 *
 * @since 6.0.0
 */
trait FilePicker
{
	public function filepicker(array $params = []): array
	{
		// fetch the parent model
		$params['model'] = $this->model();

		return (new CmsFilePicker($params))->toArray();
	}
}
