<?php

namespace Kirby\Exception;

/**
 * DuplicateException
 * Thrown when an object could not be created
 * because it already exists
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class DuplicateException extends Exception
{
    protected static $defaultKey = 'duplicate';
    protected static $defaultFallback = 'The entry exists';
    protected static $defaultHttpCode = 400;
}
