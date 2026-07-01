<?php

namespace Kirby\Cms;

use Kirby\Toolkit\HasI18n;

/**
 * Adds stringTemplate helper methods
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
trait HasStringTemplate
{
	use HasI18n;

	/**
	 * Returns the parent model the template is resolved against
	 */
	abstract public function model(): ModelWithContent|null;

	/**
	 * Parses a string template in the given value
	 * @return ($string is null ? null : string)
	 */
	protected function stringTemplate(
		string|null $string = null,
		bool $safe = true
	): string|null {
		if ($string === null || $string === '') {
			return $string;
		}

		$model = $this->model();

		/** @psalm-suppress TypeDoesNotContainNull */
		if ($model === null) {
			return $string;
		}

		return match ($safe) {
			true  => $model->toSafeString($string),
			false => $model->toString($string)
		};
	}

	/**
	 * @return ($string is null ? null : string)
	 */
	protected function stringTemplateI18n(
		array|string|null $string = null,
		bool $safe = true
	): string|null {
		if ($string === null || $string === '') {
			return $string;
		}

		return $this->stringTemplate($this->i18n($string), $safe);
	}
}
