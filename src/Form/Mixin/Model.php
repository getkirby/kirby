<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;

/**
 * Provides access to the content model and the Kirby instance
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Model
{
	protected ModelWithContent|null $model;

	/**
	 * Returns the Kirby instance
	 */
	public function kirby(): App
	{
		return $this->model()->kirby();
	}

	/**
	 * Returns the parent model
	 */
	public function model(): ModelWithContent
	{
		return $this->model ?? App::instance()->site();
	}

	/**
	 * Sets the parent model
	 */
	public function setModel(ModelWithContent|null $model): static
	{
		$this->model = $model;
		return $this;
	}
}
