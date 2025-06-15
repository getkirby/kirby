<?php

namespace Kirby\Panel\Lab;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Docs for Vue components
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @internal
 * @codeCoverageIgnore
 */
class Docs
{
	/**
	 * Returns list of all component docs
	 * for the Lab index view
	 */
	public static function all(): array
	{
		$docs  = [];
		$dist  = static::root();
		$tmp   = static::root(true);
		$files = Dir::inventory($dist)['files'];

		if (Dir::exists($tmp) === true) {
			$files = [...$files, ...Dir::inventory($tmp)['files']];
		}

		$docs = A::map(
			$files,
			function ($file) {
				$component = 'k-' . Str::camelToKebab(F::name($file['filename']));
				return Doc::factory($component)?->toItem();
			}
		);

		$docs = array_filter($docs);
		usort($docs, fn ($a, $b) => $a['text'] <=> $b['text']);

		return $docs;
	}

	/**
	 * Whether the Lab docs are installed
	 */
	public static function isInstalled(): bool
	{
		return Dir::exists(static::root()) === true;
	}

	/**
	 * Returns the root path to directory where
	 * the JSON files for each component are stored by vite
	 */
	public static function root(bool $tmp = false): string
	{
		return App::instance()->root('panel') . '/' . match ($tmp) {
			true    => 'tmp',
			default => 'dist/ui',
		};
	}
}
