<?php

namespace Kirby\Exception;

/**
 * InvalidArgumentException
 * Thrown when a method was called with invalid arguments
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class InvalidArgumentException extends Exception
{
    protected static $defaultKey = 'invalidArgument';
    protected static $defaultFallback = 'Invalid argument "{ argument }" in method "{ method }"';
    protected static $defaultHttpCode = 400;
    protected static $defaultData = ['argument' => null, 'method' => null];
}
