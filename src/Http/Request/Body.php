<?php

namespace Kirby\Http\Request;

use Stringable;

/**
 * The Body class parses the
 * request body and provides a nice
 * interface to get values from
 * structured bodies (json encoded or form data)
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Body implements Stringable
{
	use Data;

	/**
	 * The raw body content
	 */
	protected string|array|null $contents;

	/**
	 * The parsed content as array
	 */
	protected array|null $data = null;

	/**
	 * Creates a new request body object.
	 * You can pass your own array or string.
	 * If null is being passed, the class will
	 * fetch the body either from the $_POST global
	 * or from php://input.
	 */
	public function __construct(array|string|null $contents = null)
	{
		$this->contents = $contents;
	}

	/**
	 * Fetches the raw contents for the body
	 * or uses the passed contents.
	 */
	public function contents(): string|array
	{
		if ($this->contents !== null) {
			return $this->contents;
		}

		if (empty($_POST) === false) {
			return $this->contents = $_POST;
		}

		return $this->contents = file_get_contents('php://input');
	}

	/**
	 * Parses the raw contents once and caches
	 * the result. The parser will try to convert
	 * the body with the json decoder first and
	 * then run parse_str to get some results
	 * if the json decoder failed.
	 */
	public function data(): array
	{
		if (is_array($this->data) === true) {
			return $this->data;
		}

		$contents = $this->contents();

		// return content which is already in array form
		if (is_array($contents) === true) {
			return $this->data = $contents;
		}

		// try to convert the body from json
		$json = json_decode($contents, true);

		if (is_array($json) === true) {
			return $this->data = $json;
		}

		if (str_contains($contents, '=') === true) {
			// try to parse the body as query string
			parse_str($contents, $parsed);

			if (is_array($parsed)) {
				return $this->data = $parsed;
			}
		}

		return $this->data = [];
	}

	/**
	 * Converts the data array back
	 * to a http query string
	 */
	public function toString(): string
	{
		return http_build_query($this->data());
	}

	/**
	 * Magic string converter
	 */
	public function __toString(): string
	{
		return $this->toString();
	}
}
