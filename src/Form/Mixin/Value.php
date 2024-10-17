<?php

namespace Kirby\Form\Mixin;

use Kirby\Data\Data;
use Throwable;

/**
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Value
{
	/**
	 * @deprecated 3.5.0
	 * @todo remove when the general field class setup has been refactored
	 *
	 * Returns the field data
	 * in a format to be stored
	 * in Kirby's content fields
	 */
	public function default(): mixed
	{
		if (is_string($this->default) === false) {
			return $this->default;
		}

		return $this->model->toString($this->default);
	}

	/**
	 * Checks if the field is empty
	 */
	public function isEmpty(): bool
	{
		return $this->isEmptyValue($this->value());
	}

	/**
	 * Checks if the given value is considered empty
	 */
	public function isEmptyValue(mixed $value = null): bool
	{
		return in_array($value, [null, '', []], true);
	}

	/**
	 * Returns the value of the field if saveable
	 * otherwise it returns null
	 */
	public function value(bool $default = false): mixed
	{
		if ($this->isSaveable() === false) {
			return null;
		}

		if ($default === true && $this->isEmpty() === true) {
			return $this->default();
		}

		return $this->value;
	}

	/**
	 * Decodes a JSON string into an array
	 */
	protected function valueFromJson(mixed $value): array
	{
		try {
			return Data::decode($value, 'json');
		} catch (Throwable) {
			return [];
		}
	}

	/**
	 * Decodes a YAML string into an array
	 */
	protected function valueFromYaml(mixed $value): array
	{
		return Data::decode($value, 'yaml');
	}

	/**
	 * Encodes an array into a JSON string
	 */
	protected function valueToJson(
		array|null $value = null,
		bool $pretty = false
	): string {
		$constants = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

		if ($pretty === true) {
			$constants |= JSON_PRETTY_PRINT;
		}

		return json_encode($value, $constants);
	}

	/**
	 * Encodes an array into a YAML string
	 */
	protected function valueToYaml(array|null $value = null): string
	{
		return Data::encode($value, 'yaml');
	}
}
