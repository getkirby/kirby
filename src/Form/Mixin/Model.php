<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;

trait Model
{
	public ModelWithContent|null $model;

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
		return $this->model ??= App::instance()->site();
	}

	/**
	 * Sets the parent model
	 */
	public function setModel(ModelWithContent|null $model = null): void
	{
		$this->model = $model;
	}
}
