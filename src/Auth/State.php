<?php

namespace Kirby\Auth;

/**
 * Authentication states
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
enum State: string
{
	case Active       = 'active';
	case Impersonated = 'impersonated';
	case Pending      = 'pending';
	case Inactive     = 'inactive';
}
