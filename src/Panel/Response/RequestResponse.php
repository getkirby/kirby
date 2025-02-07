<?php

namespace Kirby\Panel\Response;

use Kirby\Data\Json;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class RequestResponse extends JsonResponse
{
	/**
	 * Returns the data as JSON
	 * Request responses are not wrapped in a custom namespace
	 */
	public function body(): string
	{
		return Json::encode($this->data(), $this->pretty());
	}

	/**
	 * Returns the full data array
	 * without additional information
	 */
	public function data(): array
	{
		return $this->data;
	}
}
