<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\PagePicker as CmsPagePicker;

/**
 * Page picker functionality for fields
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
trait PagePicker
{
	public function pagepicker(array $params = []): array
	{
		// inject the current model
		$params['model'] = $this->model();

		return (new CmsPagePicker($params))->toArray();
	}
}
