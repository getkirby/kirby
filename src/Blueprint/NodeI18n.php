<?php

namespace Kirby\Blueprint;

use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\I18n;

/**
 * Translatable node property
 *
 * @package   Kirby Blueprint
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 *
 * // TODO: include in test coverage in 3.9
 * @codeCoverageIgnore
 */
class NodeI18n extends NodeProperty
{
	public function __construct(
		public array $translations,
	) {
	}

	public static function factory($value = null): static|null
	{
		if ($value === false || $value === null) {
			return null;
		}

		if (is_array($value) === false) {
			$value = ['en' => $value];
		}

		return new static($value);
	}

	public function render(ModelWithContent $model): string|null
	{
		$locale = I18n::locale();

		if (isset($this->translations[$locale]) === true) {
			return $this->translations[$locale];
		}

		if (isset($this->translations['*']) === true) {
			$translations = $this->translations['*'];
			return I18n::translation($locale)[$translations] ?? $translations;
		}

		return $this->translations['en'] ?? null;
	}
}
