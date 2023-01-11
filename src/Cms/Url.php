<?php

namespace Kirby\Cms;

use Kirby\Http\Url as BaseUrl;

/**
 * The `Url` class extends the
 * `Kirby\Http\Url` class. In addition
 * to the methods of that class for dealing
 * with URLs, it provides a specific
 * `Url::home` method that always creates
 * the correct base URL and a template asset
 * URL builder.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Url extends BaseUrl
{
	public static string|null $home = null;

	/**
	 * Returns the Url to the homepage
	 */
	public static function home(): string
	{
		return App::instance()->url();
	}

	/**
	 * Creates an absolute Url to a template asset if it exists.
	 * This is used in the `css()` and `js()` helpers
	 */
	public static function toTemplateAsset(string $assetPath, string $extension): string|null
	{
		$kirby = App::instance();
		$page  = $kirby->site()->page();
		$path  = $assetPath . '/' . $page->template() . '.' . $extension;
		$file  = $kirby->root('assets') . '/' . $path;
		$url   = $kirby->url('assets') . '/' . $path;

		return file_exists($file) === true ? $url : null;
	}

	/**
	 * Smart resolver for internal and external urls
	 *
	 * @param array|string|null $options Either an array of options for the Uri class or a language string
	 */
	public static function to(string|null $path = null, array|string|null $options = null): string
	{
		$kirby = App::instance();
		return ($kirby->component('url'))($kirby, $path, $options);
	}
}
