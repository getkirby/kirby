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

	/**
	 * Applies the default text and value queries
	 *
	 * @return $this
	 */
	public function defaults(): static
	{
		$this->text  ??= '{{ item.value }}';
		$this->value ??= '{{ item.key }}';
		return $this;
	}

	/**
	 * Creates a new instance from the URL
	 * or an array of props
	 */
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
	 * or local file
	 */
	public function load(ModelWithContent $model): array|null
	{
		// resolve query templates in $this->url string
		$url = $model->toSafeString($this->url);

		// URL, request via cURL; the host in the blueprint is
		// trusted (it may well be a local or intranet host), but a
		// host that was injected by a query template is not
		if (Url::isAbsolute($url) === true) {
			$safe = Url::sameHost($url, $this->url) === false;

			return Remote::get($url, ['safe' => $safe])->json();
		}

		// local file
		return Json::read($url);
	}

	/**
	 * Normalizes the URL string or props array,
	 * including the `fetch` alias for `query`
	 */
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
	 * @param $safeMode Whether to escape special HTML characters in
	 *                 the option text for safe output in the Panel;
	 *                 only set to `false` if the text is later escaped!
	 */
	public function resolve(ModelWithContent $model, bool $safeMode = true): Options
	{
		// use cached options if present
		// @codeCoverageIgnoreStart
		if ($this->options !== null) {
			return $this->options;
		}
		// @codeCoverageIgnoreEnd

		$this->defaults();

		$data = $this->load($model);

		// @codeCoverageIgnoreStart
		if ($data === null) {
			throw new NotFoundException(
				message: 'Options could not be loaded from API: ' . $model->toSafeString($this->url)
			);
		}
		// @codeCoverageIgnoreEnd

		// turn data into Nest so that it can be queried
		// or field methods applied to the data
		$data = Nest::create($data);

		// optionally query a substructure inside the data array
		$data    = Query::factory($this->query)->resolve($data);
		$options = [];

		// text is only a raw string when using {< >}
		// or when the safe mode is explicitly disabled (select field)
		$method = $safeMode === true ? 'toSafeHtmlString' : 'toString';

		foreach ($data as $key => $item) {
			// convert simple `key: value` API data
			if (is_string($item) === true) {
				$item = new Field(null, $key, $item);
			}

			$options[] = [
				// value is always a raw string
				'value' => $model->toString($this->value, ['item' => $item]),
				'text'  => $model->$method($this->text, ['item' => $item]),
				'icon'  => $this->icon !== null ? $model->toString($this->icon, ['item' => $item]) : null,
				'info'  => $this->info !== null ? $model->$method($this->info, ['item' => $item]) : null
			];
		}

		// create Options object and render this subsequently;
		// ensure not to resolve Kirby queries again
		// to prevent double-resolution of user-controlled content
		return $this->options = Options::factory($options, resolve: false);
	}
}
