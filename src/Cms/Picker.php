<?php

namespace Kirby\Cms;

/**
 * The Picker abstract is the foundation
 * for the UserPicker, PagePicker and FilePicker
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Picker
{
	protected App $kirby;
	protected array $options;
	protected Site $site;

	/**
	 * Creates a new Picker instance
	 */
	public function __construct(array $params = [])
	{
		$this->options = [...$this->defaults(), ...$params];
		$this->kirby   = $this->options['model']->kirby();
		$this->site    = $this->kirby->site();
	}

	/**
	 * Return the array of default values
	 */
	protected function defaults(): array
	{
		// default params
		return [
			// image settings (ratio, cover, etc.)
			'image' => [],
			// query template for the info field
			'info' => false,
			// listing style: list, cards, cardlets
			'layout' => 'list',
			// number of users displayed per pagination page
			'limit' => 20,
			// optional mapping function for the result array
			'map' => null,
			// the reference model
			'model' => App::instance()->site(),
			// current page when paginating
			'page' => 1,
			// a query string to fetch specific items
			'query' => null,
			// search query
			'search' => null,
			// query template for the text field
			'text' =>  null
		];
	}

	/**
	 * Fetches all items for the picker
	 */
	abstract public function items(): Collection|null;

	/**
	 * Converts all given items to an associative
	 * array that is already optimized for the
	 * panel picker component.
	 */
	public function itemsToArray(Collection|null $items = null): array
	{
		if ($items === null) {
			return [];
		}

		$result = [];

		foreach ($items as $index => $item) {
			if (empty($this->options['map']) === false) {
				$result[] = $this->options['map']($item);
			} else {
				$result[] = $item->panel()->pickerData([
					'image'  => $this->options['image'],
					'info'   => $this->options['info'],
					'layout' => $this->options['layout'],
					'model'  => $this->options['model'],
					'text'   => $this->options['text'],
				]);
			}
		}

		return $result;
	}

	/**
	 * Apply pagination to the collection
	 * of items according to the options.
	 */
	public function paginate(Collection $items): Collection
	{
		return $items->paginate([
			'limit' => $this->options['limit'],
			'page'  => $this->options['page']
		]);
	}

	/**
	 * Return the most relevant pagination
	 * info as array
	 */
	public function paginationToArray(Pagination $pagination): array
	{
		return [
			'limit' => $pagination->limit(),
			'page'  => $pagination->page(),
			'total' => $pagination->total()
		];
	}

	/**
	 * Search through the collection of items
	 * if not deactivate in the options
	 */
	public function search(Collection $items): Collection
	{
		if (empty($this->options['search']) === false) {
			return $items->search($this->options['search']);
		}

		return $items;
	}

	/**
	 * Returns an associative array
	 * with all information for the picker.
	 * This will be passed directly to the API.
	 */
	public function toArray(): array
	{
		$items = $this->items();

		return [
			'data'       => $this->itemsToArray($items),
			'pagination' => $this->paginationToArray($items->pagination()),
		];
	}
}
