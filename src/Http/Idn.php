<?php

namespace Kirby\Http;

use TrueBV\Punycode;

/**
 * Handles Internationalized Domain Names
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
*/
class Idn
{
    public static function decode(string $domain)
    {
        return (new Punycode())->decode($domain);
    }

    public static function encode(string $domain)
    {
        return (new Punycode())->encode($domain);
    }
}
