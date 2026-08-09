<?php

namespace Kirby\Exception;

/**
 * Thrown when the current model action cannot be
 * executed due to system logic issues
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class AbilityException extends LogicException
{
	protected static string $defaultKey = 'ability';
	protected static string $defaultFallback = 'This action cannot be executed';
	protected static int $defaultHttpCode = 403;
}
