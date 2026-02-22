<?php

namespace Kirby\Exception;

/**
 * Thrown when no matching user was found
 */
class UserNotFoundException extends NotFoundException
{
	protected static string $defaultKey = 'user.notFound';

	public function __construct(string $name)
	{
		parent::__construct(data: ['name' => $name]);
	}
}
