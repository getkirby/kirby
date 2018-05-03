<?php

namespace Kirby\Http;

use Kirby\Http\Acceptance\Language;
use Kirby\Http\Acceptance\MimeType;

/**
 * The Visitor class makes it easy to inspect information
 * like the ip address, language, user agent and more
 * of the current visitor
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
*/
class Visitor
{

    /**
     * IP address
     * @var string|null
     */
    protected $ip;

    /**
     * user agent
     * @var string|null
     */
    protected $userAgent;

    /**
     * accepted language
     * @var Language|null
     */
    protected $acceptedLanguage;

    /**
     * accepted mime type
     * @var MimeType|null
     */
    protected $acceptedMimeType;

    /**
     * Creates a new visitor object.
     * Optional arguments can be passed to
     * modify the information about the visitor.
     *
     * By default everything is pulled from $_SERVER
     *
     * @param array $arguments
     */
    public function __construct(array $arguments = [])
    {
        $this->ip($arguments['ip'] ?? getenv('REMOTE_ADDR'));
        $this->userAgent($arguments['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->acceptedLanguage($arguments['acceptedLanguage'] ?? $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
        $this->acceptedMimeType($arguments['acceptedMimeType'] ?? $_SERVER['HTTP_ACCEPT'] ?? '');
    }

    /**
     * Sets the ip address if provided
     * or returns the ip of the current
     * visitor otherwise
     *
     * @param  string|null $ip
     * @return string|Visitor|null
     */
    public function ip(string $ip = null)
    {
        if ($ip === null) {
            return $this->ip;
        }
        $this->ip = $ip;
        return $this;
    }

    /**
     * Sets the user agent if provided
     * or returns the user agent string of
     * the current visitor otherwise
     *
     * @param  string|null $userAgent
     * @return string|Visitor|null
     */
    public function userAgent(string $userAgent = null)
    {
        if ($userAgent === null) {
            return $this->userAgent;
        }
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Sets the accepted language if
     * provided or returns the user's
     * accepted language otherwise
     *
     * @param  string|null $acceptedLanguage
     * @return Language|Visitor|null
     */
    public function acceptedLanguage(string $acceptedLanguage = null)
    {
        if ($acceptedLanguage === null) {
            return $this->acceptedLanguage;
        }

        $this->acceptedLanguage = new Language($acceptedLanguage);
        return $this;
    }

    /**
     * Sets the accepted mime type if
     * provided or returns the user's
     * accepted mime type otherwise
     *
     * @param  string|null $acceptedMimeType
     * @return MimeType|Visitor|null
     */
    public function acceptedMimeType(string $acceptedMimeType = null)
    {
        if ($acceptedMimeType === null) {
            return $this->acceptedMimeType;
        }

        $this->acceptedMimeType = new MimeType($acceptedMimeType);
        return $this;
    }

    public function acceptance($mimeType): float
    {
        return (new MimeType($mimeType))->quality();
    }

    /**
     * Checks if the user accepts the given mime type
     *
     * @param  string $mimeType
     * @return boolean
     */
    public function accepts(string $mimeType): bool
    {
        return $this->acceptedMimeType()->has($mimeType);
    }
}
