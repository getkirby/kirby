<?php

namespace Kirby\Exception;

/**
 * Thrown when a form or value validation fails
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class FormValidationException extends InvalidArgumentException
{
	protected static string $defaultKey = 'form.validation';
	protected static string $defaultFallback = 'Form validation failed';
	protected static int $defaultHttpCode = 400;
	protected static array $defaultData = [];
}
