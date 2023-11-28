<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Component;

/**
 * Section
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Section extends Component
{
	/**
	 * Registry for all component mixins
	 */
	public static array $mixins = [];

	/**
	 * Registry for all component types
	 */
	public static array $types = [];

	/**
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __construct(string $type, array $attrs = [])
	{
		if (isset($attrs['model']) === false) {
			throw new InvalidArgumentException('Undefined section model');
		}

		if ($attrs['model'] instanceof ModelWithContent === false) {
			throw new InvalidArgumentException('Invalid section model');
		}

		// use the type as fallback for the name
		$attrs['name'] ??= $type;
		$attrs['type']   = $type;

		parent::__construct($type, $attrs);
	}

	public function errors(): array
	{
		if (array_key_exists('errors', $this->methods) === true) {
			return $this->methods['errors']->call($this);
		}

		return $this->errors ?? [];
	}

	public function kirby(): App
	{
		return $this->model()->kirby();
	}

	public function model(): ModelWithContent
	{
		return $this->model;
	}

	public function toArray(): array
	{
		$array = parent::toArray();

		unset($array['model']);

		return $array;
	}

	public function toResponse(): array
	{
		return array_merge([
			'status' => 'ok',
			'code'   => 200,
			'name'   => $this->name,
			'type'   => $this->type
		], $this->toArray());
	}
}
