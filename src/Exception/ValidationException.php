<?php

namespace Kirby\Exception;

/**
 * Thrown when a form or value validation fails
 *
 * @package   Kirby Exception
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class ValidationException extends InvalidArgumentException
{
	protected static string $defaultKey = 'validation';
	protected static string $defaultFallback = 'The validation failed';
	protected static int $defaultHttpCode = 400;
	protected static array $defaultData = [];
}
