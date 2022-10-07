<?php

namespace Kirby\Kql;

use Kirby\Query\Query as BaseQuery;

/**
 * Extends the regular Query class with KQL's
 * specific interceptor call
 *
 * @package   Kirby Kql
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Query extends BaseQuery
{
	/**
	 * Check for each query segment whether
	 * the call should be intercepted (e.g.
	 * due to blocklisted methods)
	 */
	public function intercept($result)
	{
		return Interceptor::replace($result);
	}
}
