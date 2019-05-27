<?php

namespace Kirby\Http;

use TrueBV\Punycode;

/**
 * Handles Internationalized Domain Names
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
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
