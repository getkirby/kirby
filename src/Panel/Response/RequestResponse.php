<?php

namespace Kirby\Panel\Response;

use Kirby\Http\Response;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class RequestResponse extends JsonResponse
{
	/**
	 * Returns the full data array
	 * without additional information
	 */
	public function data(): array
	{
		return $this->data;
	}

	/**
	 * Creates a response object from mixed input
	 */
	public static function from(mixed $data): Response
	{
		// an empty list is a valid result for requests
		if ($data === []) {
			return new static([]);
		}

		return parent::from($data);
	}

	/**
	 * Request responses are not wrapped in a key namespace
	 */
	protected function wrap(): array
	{
		return $this->data();
	}
}
