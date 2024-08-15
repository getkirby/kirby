<?php

namespace Kirby\Exception;

/**
 * Thrown to trigger the CMS error page
 *
 * @package   Kirby Exception
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since 3.3.0
 */
class ErrorPageException extends Exception
{
	protected static string $defaultKey = 'errorPage';
	protected static string $defaultFallback = 'Triggered error page';
	protected static int $defaultHttpCode = 404;
}
