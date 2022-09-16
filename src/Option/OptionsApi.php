<?php

namespace Kirby\Option;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Nest;
use Kirby\Data\Json;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Toolkit\Query;

/**
 * Options fetched from any REST API
 * or local file with valid JSON data.
 *
 * @package   Kirby Option
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * 			  Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class OptionsApi extends OptionsProvider
{
	public function __construct(
		public string $url,
		public string|null $query = null,
		public string|null $text = null,
		public string|null $value = null
	) {
	}

	public function defaults(): static
	{
		$this->text  ??= '{{ item.value }}';
		$this->value ??= '{{ item.key }}';
		return $this;
	}

	public static function factory(string|array $props): static
	{
		if (is_string($props) === true) {
			return new static(url: $props);
		}

		return new static(
			url: $props['url'],
			query: $props['query'] ?? $props['fetch'] ?? null,
			text: $props['text'] ?? null,
			value: $props['value'] ?? null
		);
	}

	/**
	 * Loads the API content from a remote URL
	 * or local file (or from cache)
	 */
	public function load(ModelWithContent $model): array|null
	{
		// resolve query templates in $this->url string
		$url = $model->toSafeString($this->url);

		// URL, request via cURL
		if (Url::isAbsolute($url) === true) {
			return Remote::get($url)->json();
		}

		// local file
		return Json::read($url);
	}

	public static function polyfill(array|string $props = []): array
	{
		if (is_string($props) === true) {
			return ['url' => $props];
		}

		if ($query = $props['fetch'] ?? null) {
			$props['query'] ??= $query;
			unset($props['fetch']);
		}

		return $props;
	}

	/**
	 * Creates the actual options by loading
	 * data from the API and resolving it to
	 * the correct text-value entries
	 */
	public function resolve(ModelWithContent $model): Options
	{
		// use cached options if present
		// @codeCoverageIgnoreStart
		if ($this->options !== null) {
			return $this->options;
		}
		// @codeCoverageIgnoreEnd

		// apply property defaults
		$this->defaults();

		// load data from URL and narrow down to queried part
		$data = $this->load($model);

		if ($data === null) {
			throw new NotFoundException('Options could not be loaded from API: ' . $model->toSafeString($this->url));
		}

		$data = (new Query($this->query, Nest::create($data)))->result();

		// create options by resolving text and value query strings
		// for each item from the data
		$options = $data->toArray(fn ($item) => [
			// value is always a raw string
			'value' => $model->toString($this->value, ['item' => $item]),
			// text is only a raw string when using {< >}
			'text' => $model->toSafeString($this->text, ['item' => $item]),
		]);

		// create Options object and render this subsequently
		return $this->options = Options::factory($options);
	}
}
