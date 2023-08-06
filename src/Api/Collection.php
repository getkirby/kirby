<?php

namespace Kirby\Api;

use Closure;
use Exception;
use Kirby\Toolkit\Collection as BaseCollection;
use Kirby\Toolkit\Str;

/**
 * The Collection class is a wrapper
 * around our Kirby Collections and handles
 * stuff like pagination and proper JSON output
 * for collections in REST calls.
 *
 * @package   Kirby Api
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Collection
{
	protected string|null $model;
	protected array|null $select = null;
	protected string|null $view;

	/**
	 * Collection constructor
	 *
	 * @throws \Exception
	 */
	public function __construct(
		protected Api $api,
		protected BaseCollection|array|null $data,
		array $schema
	) {
		$this->model = $schema['model'] ?? null;
		$this->view  = $schema['view'] ?? null;

		if ($data === null) {
			if (($schema['default'] ?? null) instanceof Closure === false) {
				throw new Exception('Missing collection data');
			}

			$this->data = $schema['default']->call($this->api);
		}

		if (
			isset($schema['type']) === true &&
			$this->data instanceof $schema['type'] === false
		) {
			throw new Exception('Invalid collection type');
		}
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function select($keys = null): static
	{
		if ($keys === false) {
			return $this;
		}

		if (is_string($keys)) {
			$keys = Str::split($keys);
		}

		if ($keys !== null && is_array($keys) === false) {
			throw new Exception('Invalid select keys');
		}

		$this->select = $keys;
		return $this;
	}

	/**
	 * @throws \Kirby\Exception\NotFoundException
	 * @throws \Exception
	 */
	public function toArray(): array
	{
		$result = [];

		foreach ($this->data as $item) {
			$model = $this->api->model($this->model, $item);

			if ($this->view !== null) {
				$model = $model->view($this->view);
			}

			if ($this->select !== null) {
				$model = $model->select($this->select);
			}

			$result[] = $model->toArray();
		}

		return $result;
	}

	/**
	 * @throws \Kirby\Exception\NotFoundException
	 * @throws \Exception
	 */
	public function toResponse(): array
	{
		if ($query = $this->api->requestQuery('query')) {
			$this->data = $this->data->query($query);
		}

		if (!$this->data->pagination()) {
			$this->data = $this->data->paginate([
				'page'  => $this->api->requestQuery('page', 1),
				'limit' => $this->api->requestQuery('limit', 100)
			]);
		}

		$pagination = $this->data->pagination();

		if ($select = $this->api->requestQuery('select')) {
			$this->select($select);
		}

		if ($view = $this->api->requestQuery('view')) {
			$this->view($view);
		}

		return [
			'code'       => 200,
			'data'       => $this->toArray(),
			'pagination' => [
				'page'   => $pagination->page(),
				'total'  => $pagination->total(),
				'offset' => $pagination->offset(),
				'limit'  => $pagination->limit(),
			],
			'status' => 'ok',
			'type'   => 'collection'
		];
	}

	/**
	 * @return $this
	 */
	public function view(string $view): static
	{
		$this->view = $view;
		return $this;
	}
}
