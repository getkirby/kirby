<?php

namespace Kirby\Panel\Response;

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
	 * Request responses are not wrapped in a key namespace
	 */
	protected function wrap(): array
	{
		return $this->data();
	}
}
