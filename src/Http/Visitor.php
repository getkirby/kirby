<?php

namespace Kirby\Http;

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
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
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
     * @var string|null
     */
    protected $acceptedLanguage;

    /**
     * accepted mime type
     * @var string|null
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
     * Sets the accepted language if
     * provided or returns the user's
     * accepted language otherwise
     *
     * @param string|null $acceptedLanguage
     * @return \Kirby\Toolkit\Obj|\Kirby\Http\Visitor|null
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
     * @return \Kirby\Toolkit\Collection
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
     * Checks if the user accepts the given language
     *
     * @param string $code
     * @return bool
     */
    public function acceptsLanguage(string $code): bool
    {
        $mode = Str::contains($code, '_') === true ? 'locale' : 'code';

        foreach ($this->acceptedLanguages() as $language) {
            if ($language->$mode() === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the accepted mime type if
     * provided or returns the user's
     * accepted mime type otherwise
     *
     * @param string|null $acceptedMimeType
     * @return \Kirby\Toolkit\Obj|\Kirby\Http\Visitor
     */
    public function acceptedMimeType(string $acceptedMimeType = null)
    {
        if ($acceptedMimeType === null) {
            return $this->acceptedMimeTypes()->first();
        }

        $this->acceptedMimeType = $acceptedMimeType;
        return $this;
    }

    /**
     * Returns a collection of all accepted mime types
     *
     * @return \Kirby\Toolkit\Collection
     */
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
     * Checks if the user accepts the given mime type
     *
     * @param string $mimeType
     * @return bool
     */
    public function acceptsMimeType(string $mimeType): bool
    {
        return Mime::isAccepted($mimeType, $this->acceptedMimeType);
    }

    /**
     * Returns the MIME type from the provided list that
     * is most accepted (= preferred) by the visitor
     * @since 3.3.0
     *
     * @param string ...$mimeTypes MIME types to query for
     * @return string|null Preferred MIME type
     */
    public function preferredMimeType(string ...$mimeTypes): ?string
    {
        foreach ($this->acceptedMimeTypes() as $acceptedMime) {
            // look for direct matches
            if (in_array($acceptedMime->type(), $mimeTypes)) {
                return $acceptedMime->type();
            }

            // test each option against wildcard `Accept` values
            foreach ($mimeTypes as $expectedMime) {
                if (Mime::matches($expectedMime, $acceptedMime->type()) === true) {
                    return $expectedMime;
                }
            }
        }

        return null;
    }

    /**
     * Returns true if the visitor prefers a JSON response over
     * an HTML response based on the `Accept` request header
     * @since 3.3.0
     *
     * @return bool
     */
    public function prefersJson(): bool
    {
        return $this->preferredMimeType('application/json', 'text/html') === 'application/json';
    }

    /**
     * Sets the ip address if provided
     * or returns the ip of the current
     * visitor otherwise
     *
     * @param string|null $ip
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
     * @param string|null $userAgent
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
}
