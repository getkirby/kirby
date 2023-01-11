<?php

namespace Kirby\Option;

use Kirby\Cms\Block;
use Kirby\Cms\Field;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\StructureObject;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;

/**
 * Options derrived from running a query against
 * pages, files, users or structures to create
 * options out of them.
 *
 * @package   Kirby Option
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * 			  Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class OptionsQuery extends OptionsProvider
{
	public function __construct(
		public string $query,
		public string|null $text = null,
		public string|null $value = null
	) {
	}

	protected function collection(array $array): Collection
	{
		foreach ($array as $key => $value) {
			if (is_scalar($value) === true) {
				$array[$key] = new Obj([
					'key'   => new Field(null, 'key', $key),
					'value' => new Field(null, 'value', $value),
				]);
			}
		}

		return new Collection($array);
	}

	public static function factory(string|array $props): static
	{
		if (is_string($props) === true) {
			return new static(query: $props);
		}

		return new static(
			query: $props['query'] ?? $props['fetch'],
			text: $props['text'] ?? null,
			value: $props['value'] ?? null
		);
	}

	/**
	 * Returns defaults for the following based on item type:
	 * [query entry alias, default text query, default value query]
	 */
	protected function itemToDefaults(array|object $item): array
	{
		return match (true) {
			is_array($item),
			$item instanceof Obj => [
				'arrayItem',
				'{{ item.value }}',
				'{{ item.value }}'
			],

			$item instanceof StructureObject => [
				'structureItem',
				'{{ item.title }}',
				'{{ item.id }}'
			],

			$item instanceof Block => [
				'block',
				'{{ block.type }}: {{ block.id }}',
				'{{ block.id }}'
			],

			$item instanceof Page => [
				'page',
				'{{ page.title }}',
				'{{ page.id }}'
			],

			$item instanceof File => [
				'file',
				'{{ file.filename }}',
				'{{ file.id }}'
			],

			$item instanceof User => [
				'user',
				'{{ user.username }}',
				'{{ user.email }}'
			],

			default => [
				'item',
				'{{ item.value }}',
				'{{ item.value }}'
			]
		};
	}

	public static function polyfill(array|string $props = []): array
	{
		if (is_string($props) === true) {
			return ['query' => $props];
		}

		if ($query = $props['fetch'] ?? null) {
			$props['query'] ??= $query;
			unset($props['fetch']);
		}

		return $props;
	}

	/**
	 * Creates the actual options by running
	 * the query on the model and resolving it to
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

		// run query
		$result = $model->query($this->query);

		// the query already returned an options collection
		if ($result instanceof Options) {
			return $result;
		}

		// convert result to a collection
		if (is_array($result) === true) {
			$result = $this->collection($result);
		}

		if ($result instanceof Collection === false) {
			throw new InvalidArgumentException('Invalid query result data: ' . get_class($result));
		}

		// create options array
		$options = $result->toArray(function ($item) use ($model) {
			// get defaults based on item type
			[$alias, $text, $value] = $this->itemToDefaults($item);
			$data = ['item' => $item, $alias => $item];

			// value is always a raw string
			$value = $model->toString($this->value ?? $value, $data);

			// text is only a raw string when HTML prop
			// is explicitly set to true
			$text = $model->toSafeString($this->text ?? $text, $data);

			return compact('text', 'value');
		});

		return $this->options = Options::factory($options);
	}
}
