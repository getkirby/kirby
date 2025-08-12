<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\FilePicker as CmsFilePicker;

/**
 * File picker functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 * @since 6.0.0
 *
 * @package   Kirby Form
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
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
