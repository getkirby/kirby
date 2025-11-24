<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;

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
	protected function setModel(ModelWithContent|null $model): void
	{
		$this->model = $model;
	}
}
