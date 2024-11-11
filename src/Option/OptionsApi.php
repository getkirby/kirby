<?php

namespace Kirby\Option;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Nest;
use Kirby\Content\Field;
use Kirby\Data\Json;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Query\Query;

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
		public string|null $value = null,
		public string|null $icon = null,
		public string|null $info = null
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
			url  : $props['url'],
			query: $props['query'] ?? $props['fetch'] ?? null,
			text : $props['text'] ?? null,
			value: $props['value'] ?? null,
			icon : $props['icon'] ?? null,
			info : $props['info'] ?? null
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
	 *
	 * @param bool $safeMode Whether to escape special HTML characters in
	 *                       the option text for safe output in the Panel;
	 *                       only set to `false` if the text is later escaped!
	 */
	public function resolve(ModelWithContent $model, bool $safeMode = true): Options
	{
		// use cached options if present
		// @codeCoverageIgnoreStart
		if ($this->options !== null) {
			return $this->options;
		}
		// @codeCoverageIgnoreEnd

		// apply property defaults
		$this->defaults();

		// load data from URL and convert from JSON to array
		$data = $this->load($model);

		// @codeCoverageIgnoreStart
		if ($data === null) {
			throw new NotFoundException('Options could not be loaded from API: ' . $model->toSafeString($this->url));
		}
		// @codeCoverageIgnoreEnd

		// turn data into Nest so that it can be queried
		// or field methods applied to the data
		$data = Nest::create($data);

		// optionally query a substructure inside the data array
		$data    = Query::factory($this->query)->resolve($data);
		$options = [];

		// create options by resolving text and value query strings
		// for each item from the data
		foreach ($data as $key => $item) {
			// convert simple `key: value` API data
			if (is_string($item) === true) {
				$item = new Field(null, $key, $item);
			}

			$safeMethod = $safeMode === true ? 'toSafeString' : 'toString';

			$options[] = [
				// value is always a raw string
				'value' => $model->toString($this->value, ['item' => $item]),
				// text is only a raw string when using {< >}
				// or when the safe mode is explicitly disabled (select field)
				'text'  => $model->$safeMethod($this->text, ['item' => $item]),
				// additional data
				'icon'  => $this->icon !== null ? $model->toString($this->icon, ['item' => $item]) : null,
				'info'  => $this->info !== null ? $model->$safeMethod($this->info, ['item' => $item]) : null
			];
		}

		// create Options object and render this subsequently
		return $this->options = Options::factory($options);
	}
}
