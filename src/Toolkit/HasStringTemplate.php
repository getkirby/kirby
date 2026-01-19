<?php

namespace Kirby\Toolkit;

/**
 * Adds stringTemplate helper methods
 *
 * @package   Kirby Toolkit
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
trait HasStringTemplate
{
	use HasI18n;

	/**
	 * Parses a string template in the given value
	 */
	protected function stringTemplate(
		string|null $string = null,
		bool $safe = true
	): string|null {
		if ($string === null || $string === '') {
			return $string;
		}

		$model = $this->model();

		if ($model === null) {
			return $string;
		}

		return match ($safe) {
			true  => $model->toSafeString($string),
			false => $model->toString($string)
		};
	}

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
