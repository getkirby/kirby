<?php

namespace Kirby\Http;

use Kirby\Toolkit\Str;

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
    /**
     * Convert domain name from IDNA ASCII to Unicode
     *
     * @param string $domain
     * @return string|false
     */
    public static function decode(string $domain)
    {
        return idn_to_utf8($domain);
    }

    /**
     * Convert domain name to IDNA ASCII form
     *
     * @param string $domain
     * @return string|false
     */
    public static function encode(string $domain)
    {
        return idn_to_ascii($domain);
    }

    /**
     * Decodes a email address to the Unicode format
     *
     * @param string $email
     * @return string
     */
    public static function decodeEmail(string $email): string
    {
        if (Str::contains($email, 'xn--') === true) {
            $parts   = Str::split($email, '@');
            $address = $parts[0];
            $domain  = Idn::decode($parts[1] ?? '');
            $email   = $address . '@' . $domain;
        }

        return $email;
    }

    /**
     * Encodes a email address to the Punycode format
     *
     * @param string $email
     * @return string
     */
    public static function encodeEmail(string $email): string
    {
        if (mb_detect_encoding($email, 'ASCII', true) === false) {
            $parts   = Str::split($email, '@');
            $address = $parts[0];
            $domain  = Idn::encode($parts[1] ?? '');
            $email   = $address . '@' . $domain;
        }

        return $email;
    }
}
