<?php

namespace Kirby\Exception;

/**
 * DuplicateException
 * Thrown when an object could not be created
 * because it already exists
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class DuplicateException extends Exception
{
    protected static $defaultKey = 'duplicate';
    protected static $defaultFallback = 'The entry exists';
    protected static $defaultHttpCode = 400;
}
