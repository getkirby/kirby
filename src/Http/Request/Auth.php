<?php

namespace Kirby\Http\Request;

use SensitiveParameter;

/**
 * Base class for auth types
 *
 * @package   Kirby Http
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Auth
{
	/**
	 * @param string $data Raw authentication data after the first space in the `Authorization` header
	 */
	public function __construct(
		#[SensitiveParameter]
		protected string $data
	) {
	}

	/**
	 * Converts the object to a string
	 */
	public function __toString(): string
	{
		return ucfirst($this->type()) . ' ' . $this->data();
	}

	/**
	 * Returns the raw authentication data after the
	 * first space in the `Authorization` header
	 */
	public function data(): string
	{
		return $this->data;
	}

	/**
	 * Returns the name of the auth type (lowercase)
	 */
	abstract public function type(): string;
}
