<?php

namespace Kirby\Exception;

/**
 * ErrorPageException
 * Thrown to trigger the CMS error page
 * @since 3.3.0
 *
 * @package   Kirby Exception
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class ErrorPageException extends Exception
{
	protected static $defaultKey = 'errorPage';
	protected static $defaultFallback = 'Triggered error page';
	protected static $defaultHttpCode = 404;
}
