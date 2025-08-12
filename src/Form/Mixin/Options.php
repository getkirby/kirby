<?php

namespace Kirby\Form\Mixin;

use Kirby\Field\FieldOptions;

/**
 * Options functionality for fields
 *
 * @mixin \Kirby\Form\FieldClass
 * @since 6.0.0
 *
 * @package   Kirby Form
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
trait Options
{
	/**
	 * API settings for options requests. This will only take affect when `options` is set to `api`.
	 */
	protected mixed $api;

	/**
	 * An array with options
	 */
	protected mixed $options;

	/**
	 * Query settings for options queries. This will only take affect when `options` is set to `query`.
	 */
	protected mixed $query;

	public function api(): mixed
	{
		return $this->api;
	}

	public function options(): array
	{
		return $this->getOptions();
	}

	public function query(): mixed
	{
		return $this->query;
	}

	protected function setApi(mixed $api = null): void
	{
		$this->api = $api;
	}

	protected function setOptions(mixed $options = []): void
	{
		$this->options = $options;
	}

	protected function setQuery(mixed $query = null): void
	{
		$this->query = $query;
	}

	public function getOptions(): array
	{
		$props = FieldOptions::polyfill($this->props());
		$options = FieldOptions::factory($props['options']);
		return $options->render($this->model());
	}

	public function sanitizeOption(mixed $value): mixed
	{
		$options = array_column($this->options(), 'value');
		return in_array($value, $options) ? $value : null;
	}

	public function sanitizeOptions(array $values): array
	{
		$options = array_column($this->options(), 'value');
		$options = array_intersect($values, $options);
		return array_values($options);
	}
}
