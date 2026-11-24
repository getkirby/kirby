<?php

namespace Kirby\Auth\Exception;

use Kirby\Exception\PermissionException;

/**
 * Thrown for any invalid login
 */
class LoginNotPermittedException extends PermissionException
{
	protected static string $defaultKey = 'access.login';
}
