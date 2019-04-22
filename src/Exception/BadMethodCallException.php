<?php

namespace Kirby\Exception;

/**
 * BadMethodCallException
 * Thrown when a method was called that does not exist
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class BadMethodCallException extends Exception
{
    protected static $defaultKey = 'invalidMethod';
    protected static $defaultFallback = 'The method "{ method }" does not exist';
    protected static $defaultHttpCode = 400;
    protected static $defaultData = ['method' => null];
}
