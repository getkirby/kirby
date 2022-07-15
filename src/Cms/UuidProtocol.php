<?php

namespace Kirby\Cms;

use Kirby\Http\Uri;
use Kirby\Toolkit\Str;

/**
 * Represents the uri protocol for an UUID
 *
 * ```
 * site://
 * user://user-id
 * page://12345678-90ab-cdef-1234-567890abcdef
 * file://12345678-90ab-cdef-1234-567890abcdef
 * block://12345678-90ab-cdef-1234-567890abcdef
 * struct://12345678-90ab-cdef-1234-567890abcdef
 *
 * // reference to a specific language of a page
 * page://12345678-90ab-cdef-1234-567890abcdef?lang=de
 * ```
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UuidProtocol extends Uri
{
	/**
	 * supported schemes
	 */
	protected static array $schemes = [
		'site',
		'page',
		'file',
		'user',
		'block',
		'struct'
	];

	public function __construct($props = [], array $inject = [])
	{
		// treat `site://` differently:
		// there is no host for site type, rest is always the path
		if (
			is_string($props) === true &&
			Str::startsWith($props, 'site://') === true
		) {
			return parent::__construct([
				'scheme' => 'site',
				'host'   => '',
				'path' 	 => Str::after($props, 'site://')
			]);
		}

		return parent::__construct($props, $inject);
	}

	/**
	 * Custom base method to ensure that
	 * scheme is always included
	 */
	public function base(): string|null
	{
		return $this->scheme . '://' . $this->host;
	}

	/**
	 * Returns the UUIDv4 part of the UUID protocol
	 */
	public function host(): string|null
	{
		return $this->host;
	}

	/**
	 * Return the full UUID string
	 */
	public function toString(bool $scheme = true): string
	{
		$url = parent::toString();

		// correction for site protocols,
		// since site has no host
		$url = Str::replace($url, ':///', '://');

		if ($scheme === false) {
			$url = Str::after($url, '://');
		}

		return $url;
	}

	/**
	 * Returns the scheme as model type
	 */
	public function type(): string
	{
		return $this->scheme;
	}
}
