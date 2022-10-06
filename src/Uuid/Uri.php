<?php

namespace Kirby\Uuid;

use Kirby\Http\Uri as BaseUri;
use Kirby\Toolkit\Str;

/**
 * Uri protocol for UUIDs
 * @since 3.8.0
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Uri extends BaseUri
{
	/**
	 * Supported schemes
	 */
	public static array $schemes = [
		'site',
		'page',
		'file',
		'user',
		// TODO: acitivate for uuid-block-structure-support
		// 'block',
		// 'struct'
	];

	public function __construct(array|string $props = [], array $inject = [])
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
	 * Returns the ID part of the UUID string
	 * (and sets it when new one passed)
	 */
	public function host(string $host = null): string|null
	{
		if ($host !== null) {
			return $this->host = $host;
		}

		return $this->host;
	}

	/**
	 * Return the full UUID string
	 */
	public function toString(): string
	{
		$url = parent::toString();

		// correction for protocols without host,
		// e.g. mainly `site://`
		$url = Str::replace($url, ':///', '://');

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
