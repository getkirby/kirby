<?php

namespace Kirby\Panel;

use Kirby\Http\Response;

/**
 * The Drawer response class handles Fiber
 * requests to render the JSON object for
 * Panel drawers
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Drawer extends Dialog
{
	protected static string $key = '$drawer';
}
