<?php

namespace Kirby\Toolkit\Url;

use Kirby\Toolkit\Url;

/**
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Scheme
{

    /**
     * Returns the schme for the given url
     *
     * @param  string|null  $url
     * @return string|false
     */
    public static function get(string $url = null)
    {
        if ($url === null) {
            return static::isSecure() === true ? 'https' : 'http';
        }
        return parse_url($url, PHP_URL_SCHEME);
    }

    /**
     * Returns whether current HTTP call is secured
     *
     * @return bool
     */
    public static function isSecure(): bool
    {
        return static::hasHttpsFlag() ||
               static::hasHttpsPort() ||
               static::hasHttpsProtocol();
    }

    /**
     * Returns whether HTTPS flag is set positive
     *
     * @return bool
     */
    protected static function hasHttpsFlag(): bool
    {
        return isset($_SERVER['HTTPS']) === true &&
               empty($_SERVER['HTTPS']) === false &&
               strtolower($_SERVER['HTTPS']) !== 'off';
    }

    /**
     * Returns whether HTTPS port is used
     *
     * @return bool
     */
    protected static function hasHttpsPort(): bool
    {
        return ($_SERVER['SERVER_PORT'] ?? null === '443') ||
               ($_SERVER['HTTP_X_FORWARDED_PORT'] ?? null == '443');
    }

    /**
     * Returns whether HTTPS protocol is used
     *
     * @return bool
     */
    protected static function hasHttpsProtocol(): bool
    {
        return ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null === 'https') ||
               ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null === 'https, http');
    }
}
