<?php

namespace Kirby\Content;

use Kirby\Exception\LogicException;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class LockedContentException extends LogicException
{
	protected static string $defaultKey = 'content.lock';
	protected static string $defaultFallback = 'The version is locked';
	protected static int $defaultHttpCode = 423;

	public function __construct(
		Lock $lock,
		string|null $key = null,
		string|null $message = null,
	) {
		parent::__construct(
			message: $message,
			key: $key,
			details: $lock->toArray()
		);
	}
}
