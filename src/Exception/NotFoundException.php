<?php

namespace Kirby\Exception;

/**
 * NotFoundException
 * Thrown when something was not found
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class NotFoundException extends Exception
{
    protected static $defaultKey = 'notFound';
    protected static $defaultFallback = 'Not found';
    protected static $defaultHttpCode = 404;
}
