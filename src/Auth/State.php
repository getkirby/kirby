<?php

namespace Kirby\Auth;

/**
 * Authentication states
 *
 * @since 6.0.0
 */
enum State: string
{
	case Active       = 'active';
	case Impersonated = 'impersonated';
	case Pending      = 'pending';
	case Inactive     = 'inactive';
}
