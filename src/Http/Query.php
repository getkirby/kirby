<?php

namespace Kirby\Http;

use Kirby\Toolkit\Obj;

/**
 * A wrapper around a URL query string
 * that converts it into a Kirby Obj for easier
 * access of each query attribute.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Query extends Obj
{
	public function __construct(string|array|null $query)
	{
		if (is_string($query) === true) {
			parse_str(ltrim($query, '?'), $query);
		}

		parent::__construct($query ?? []);
	}

	public function isEmpty(): bool
	{
		return empty((array)$this) === true;
	}

	public function isNotEmpty(): bool
	{
		return empty((array)$this) === false;
	}

	public function toString(bool $questionMark = false): string
	{
		$query = http_build_query($this, '', '&', PHP_QUERY_RFC3986);

		if (empty($query) === true) {
			return '';
		}

		if ($questionMark === true) {
			$query = '?' . $query;
		}

		return $query;
	}


	public function __toString(): string
	{
		return $this->toString();
	}
}
