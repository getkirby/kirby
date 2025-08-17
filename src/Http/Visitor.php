<?php

namespace Kirby\Http;

use Kirby\Filesystem\Mime;
use Kirby\Toolkit\Collection;
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
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Visitor
{
	protected string|null $ip = null;
	protected string|null $userAgent = null;
	protected string|null $acceptedLanguage = null;
	protected string|null $acceptedMimeType = null;

	/**
	 * Creates a new visitor object.
	 * Optional arguments can be passed to
	 * modify the information about the visitor.
	 *
	 * By default everything is pulled from $_SERVER
	 */
	public function __construct(array $arguments = [])
	{
		$ip         = $arguments['ip'] ?? null;
		$ip       ??= Environment::getGlobally('REMOTE_ADDR', '');
		$agent      = $arguments['userAgent'] ?? null;
		$agent    ??= Environment::getGlobally('HTTP_USER_AGENT', '');
		$language   = $arguments['acceptedLanguage'] ?? null;
		$language ??= Environment::getGlobally('HTTP_ACCEPT_LANGUAGE', '');
		$mime       = $arguments['acceptedMimeType'] ?? null;
		$mime     ??= Environment::getGlobally('HTTP_ACCEPT', '');

		$this->ip($ip);
		$this->userAgent($agent);
		$this->acceptedLanguage($language);
		$this->acceptedMimeType($mime);
	}

	/**
	 * Sets the accepted language if
	 * provided or returns the user's
	 * accepted language otherwise
	 *
	 * @return $this|\Kirby\Toolkit\Obj|null
	 */
	public function acceptedLanguage(
		string|null $acceptedLanguage = null
	): static|Obj|null {
		if ($acceptedLanguage === null) {
			return $this->acceptedLanguages()->first();
		}

		$this->acceptedLanguage = $acceptedLanguage;
		return $this;
	}

	/**
	 * Returns an array of all accepted languages
	 * including their quality and locale
	 */
	public function acceptedLanguages(): Collection
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
	 * @return $this|\Kirby\Toolkit\Obj|null
	 */
	public function acceptedMimeType(
		string|null $acceptedMimeType = null
	): static|Obj|null {
		if ($acceptedMimeType === null) {
			return $this->acceptedMimeTypes()->first();
		}

		$this->acceptedMimeType = $acceptedMimeType;
		return $this;
	}

	/**
	 * Returns a collection of all accepted mime types
	 */
	public function acceptedMimeTypes(): Collection
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
	public function preferredMimeType(string ...$mimeTypes): string|null
	{
		foreach ($this->acceptedMimeTypes() as $accepted) {
			// look for direct matches
			if (in_array($accepted->type(), $mimeTypes, true) === true) {
				return $accepted->type();
			}

			// test each option against wildcard `Accept` values
			foreach ($mimeTypes as $expected) {
				if (Mime::matches($expected, $accepted->type()) === true) {
					return $expected;
				}
			}
		}

		return null;
	}

	/**
	 * Returns true if the visitor prefers a JSON response over
	 * an HTML response based on the `Accept` request header
	 * @since 3.3.0
	 */
	public function prefersJson(): bool
	{
		$preferred = $this->preferredMimeType('application/json', 'text/html');
		return $preferred === 'application/json';
	}

	/**
	 * Sets the ip address if provided
	 * or returns the ip of the current
	 * visitor otherwise
	 *
	 * @return $this|string|null
	 */
	public function ip(string|null $ip = null): static|string|null
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
	 * @return $this|string|null
	 */
	public function userAgent(string|null $userAgent = null): static|string|null
	{
		if ($userAgent === null) {
			return $this->userAgent;
		}

		$this->userAgent = $userAgent;
		return $this;
	}
}
