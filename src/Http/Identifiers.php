<?php

namespace Kirby\Http;

use Kirby\Toolkit\Obj;

/**
 * A wrapper for URL query and params
 * @internal
 *
 * @package   Kirby Http
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Identifiers extends Obj
{
	public function __construct(string|array|null $query)
	{
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

	public function __toString(): string
	{
		return $this->toString();
	}
}
