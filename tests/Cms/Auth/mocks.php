<?php

namespace Kirby\Cms\Auth;

use Exception;
use Kirby\Auth\Challenge;
use Kirby\Cms\User;

class ErrorneousChallenge extends Challenge
{
	public static function isAvailable(User $user, string $mode): bool
	{
		return $user->email() === 'error@getkirby.com';
	}

	public function create(): string|null
	{
		throw new Exception('An error occurred in the challenge');
	}
}
