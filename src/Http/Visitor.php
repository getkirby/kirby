<?php

namespace Kirby\Http;

use Kirby\Http\Acceptance\Language;
use Kirby\Http\Acceptance\MimeType;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Mime;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;

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
            return $this->acceptedLanguages()->first();
        }

        $this->acceptedLanguage = $acceptedLanguage;
        return $this;
    }

    /**
     * Returns an array of all accepted languages
     * including their quality and locale
     *
     * @return Collection
     */
    public function acceptedLanguages()
    {
        $accepted  = Str::accepted($this->acceptedLanguage);
        $languages = [];

        foreach ($accepted as $language) {
            $value  = $language['value'];
            $parts  = Str::split($value, '-');
            $code   = isset($parts[0]) ? Str::lower($parts[0]) : null;
            $region = isset($parts[1]) ? Str::upper($parts[1]) : null;
            $locale = $region ? $code . '_' . $region : $code;

            $languages[$locale] = new Obj([
                'code'     => $code,
                'locale'   => $locale,
                'original' => $value,
                'quality'  => $language['quality'],
                'region'   => $region,
            ]);
        }

        return new Collection($languages);
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
            return $this->acceptedMimeTypes()->first();
        }

        $this->acceptedMimeType = $acceptedMimeType;
        return $this;
    }

    public function acceptedMimeTypes()
    {
        $accepted = Str::accepted($this->acceptedMimeType);
        $mimes    = [];

        foreach ($accepted as $mime) {
            $mimes[$mime['value']] = new Obj([
                'type'     => $mime['value'],
                'quality'  => $mime['quality'],
            ]);
        }

        return new Collection($mimes);

    }

    /**
     * Returns the acceptance quality for the given
     * mime type if the mime type is accepted at all.
     *
     * @param string $mimeType
     * @return float
     */
    public function acceptance(string $mimeType): float
    {
        if ($mime = $this->acceptedMimeTypes()->findBy('type', $mimeType)) {
            return $mime->quality();
        } else {
            return 0;
        }
    }

    /**
     * Checks if the user accepts the given mime type
     *
     * @param  string $mimeType
     * @return boolean
     */
    public function accepts(string $mimeType): bool
    {
        return Mime::isAccepted($mimeType, $this->acceptedMimeType);
    }
}
