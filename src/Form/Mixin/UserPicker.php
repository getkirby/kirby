<?php


namespace Kirby\Form\Mixin;

use Kirby\Cms\UserPicker as CmsUserPicker;

/**
 * User picker functionality for fields
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
trait UserPicker
{
	public function userpicker(array $params = []): array
	{
		$params['model'] = $this->model();

		return (new CmsUserPicker($params))->toArray();
	}
}
