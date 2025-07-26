<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;

/**
 * Model functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Model
{
	protected ModelWithContent $model;

	/**
	 * Returns the Kirby instance
	 */
	public function kirby(): App
	{
		return $this->model->kirby();
	}

	/**
	 * Returns the parent model
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Sets the parent model
	 */
	protected function setModel(ModelWithContent|null $model = null): void
	{
		$this->model = $model ?? App::instance()->site();
	}
}
