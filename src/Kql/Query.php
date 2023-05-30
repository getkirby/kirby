<?php

namespace Kirby\Kql;

use Kirby\Query\Query as BaseQuery;

/**
 * Extends the core Query class with the KQL-specific
 * functionalities to intercept the segments chain calls
 *
 * @package   Kirby KQL
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Query extends BaseQuery
{
	/**
	 * Intercepts the chain of segments called
	 * on each other by replacing objects with
	 * their corresponding Interceptor which
	 * handles blocking calls to restricted methods
	 */
	public function intercept(mixed $result): mixed
	{
		return is_object($result) ? Interceptor::replace($result) : $result;
	}
}
