<?php

namespace Kirby\Api;

use Closure;
use Exception;
use Kirby\Toolkit\Str;

/**
 * The API Model class can be wrapped around any
 * kind of object. Each model defines a set of properties that
 * are available in REST calls. Those properties are defined as
 * simple Closures which are resolved on demand. This is inspired
 * by GraphQLs architecture and makes it possible to load
 * only the model data that is needed for the current API call.
 *
 * @package   Kirby Api
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Model
{
	protected array $fields;
	protected array|null $select;
	protected array $views;

	/**
	 * Model constructor
	 *
	 * @throws \Exception
	 */
	public function __construct(
		protected Api $api,
		protected object|array|string|null $data,
		array $schema
	) {
		$this->fields = $schema['fields'] ?? [];
		$this->select = $schema['select'] ?? null;
		$this->views  = $schema['views']  ?? [];

		if (
			$this->select === null &&
			array_key_exists('default', $this->views)
		) {
			$this->view('default');
		}

		if ($data === null) {
			if (($schema['default'] ?? null) instanceof Closure === false) {
				throw new Exception(message: 'Missing model data');
			}

			$this->data = $schema['default']->call($this->api);
		}

		if (
			isset($schema['type']) === true &&
			$this->data instanceof $schema['type'] === false
		) {
			$class = match ($this->data) {
				null    => 'null',
				default => $this->data::class,
			};
			throw new Exception(sprintf('Invalid model type "%s" expected: "%s"', $class, $schema['type']));
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
			throw new Exception(message: 'Invalid select keys');
		}

		$this->select = $keys;
		return $this;
	}

	/**
	 * @throws \Exception
	 */
	public function selection(): array
	{
		$select    = $this->select;
		$select  ??= array_keys($this->fields);
		$selection = [];

		foreach ($select as $key => $value) {
			if (is_int($key) === true) {
				$selection[$value] = [
					'view'   => null,
					'select' => null
				];
				continue;
			}

			if (is_string($value) === true) {
				if ($value === 'any') {
					throw new Exception(message: 'Invalid sub view: "any"');
				}

				$selection[$key] = [
					'view'   => $value,
					'select' => null
				];

				continue;
			}

			if (is_array($value) === true) {
				$selection[$key] = [
					'view'   => null,
					'select' => $value
				];
			}
		}

		return $selection;
	}

	/**
	 * @throws \Kirby\Exception\NotFoundException
	 * @throws \Exception
	 */
	public function toArray(): array
	{
		$select = $this->selection();
		$result = [];

		foreach ($this->fields as $key => $resolver) {
			if (
				array_key_exists($key, $select) === false ||
				$resolver instanceof Closure === false
			) {
				continue;
			}

			$value = $resolver->call($this->api, $this->data);

			if (is_object($value)) {
				$value = $this->api->resolve($value);
			}

			if (
				$value instanceof Collection ||
				$value instanceof self
			) {
				$selection = $select[$key];

				if ($subview = $selection['view']) {
					$value->view($subview);
				}

				if ($subselect = $selection['select']) {
					$value->select($subselect);
				}

				$value = $value->toArray();
			}

			$result[$key] = $value;
		}

		ksort($result);

		return $result;
	}

	/**
	 * @throws \Kirby\Exception\NotFoundException
	 * @throws \Exception
	 */
	public function toResponse(): array
	{
		$model = $this;

		if ($select = $this->api->requestQuery('select')) {
			$model = $model->select($select);
		}

		if ($view = $this->api->requestQuery('view')) {
			$model = $model->view($view);
		}

		return [
			'code'   => 200,
			'data'   => $model->toArray(),
			'status' => 'ok',
			'type'   => 'model'
		];
	}

	/**
	 * @return $this
	 * @throws \Exception
	 */
	public function view(string $name): static
	{
		if ($name === 'any') {
			return $this->select(null);
		}

		if (isset($this->views[$name]) === false) {
			$name = 'default';

			// try to fall back to the default view at least
			if (isset($this->views[$name]) === false) {
				throw new Exception(sprintf('The view "%s" does not exist', $name));
			}
		}

		return $this->select($this->views[$name]);
	}
}
