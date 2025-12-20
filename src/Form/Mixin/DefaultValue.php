<?php

namespace Kirby\Form\Mixin;

trait DefaultValue
{
	/**
	 * Default value for the field, which will be used when a page/file/user is created
	 */
	protected mixed $default;

	/**
	 * Returns the default value of the field
	 */
	public function default(): mixed
	{
		if (isset($this->default) === false) {
			return null;
		}

		if (is_string($this->default) === false) {
			return $this->default;
		}

		return $this->model()->toString($this->default);
	}
}
