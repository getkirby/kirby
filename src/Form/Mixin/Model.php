<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;

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

	/**
	 * Parses a string template in the given value
	 */
	protected function stringTemplate(
		string|null $string = null,
		bool $safe = false
	): string|null {
		if ($string !== null) {
			return $safe === true ? $this->model->toSafeString($string) : $this->model->toString($string);
		}

		return null;
	}
}
