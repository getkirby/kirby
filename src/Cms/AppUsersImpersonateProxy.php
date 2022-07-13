<?php

namespace Kirby\Cms;

/**
 * Temporary proxy class to ease transition
 * of binding the callback for `$kirby->impersonate()`
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>,
 * 			  Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @internal
 * @deprecated Will be removed in Kirby 3.9.0
 * @todo remove in 3.9.0
 */
class AppUsersImpersonateProxy
{
	public function __construct(protected App $app)
	{
	}

	public function __call($name, $arguments)
	{
		Helpers::deprecated('Calling $kirby->' . $name . '() as $this->' . $name . '() has been deprecated inside the $kirby->impersonate() callback function. Use a dedicated $kirby object for your call instead of $this. In Kirby 3.9.0 $this will no longer refer to the $kirby object, but the current context of the callback function.');

		return $this->app->$name(...$arguments);
	}
}
