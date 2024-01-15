<?php

namespace Kirby\Cms;

use Kirby\Http\Url as BaseUrl;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

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
	public static function toTemplateAsset(
		string $assetPath,
		string $extension
	): string|null {
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
	public static function to(
		string|null $path = null,
		array|string|null $options = null
	): string {
		$kirby = App::instance();
		return ($kirby->component('url'))($kirby, $path, $options);
	}

	/**
	 * Smart resolver for internal and external urls
	 */
	public static function availableLinkTypes(): array
	{
		$kirby = App::instance();

		return [
			'anchor' => [
				'detect' => function (string $value): bool {
					return Str::startsWith($value, '#') === true;
				},
				'link' => function (string $value): string {
					return $value;
				},
				'text' => function (string $value): string {
					return $value;
				},
				'validate' => function (string $value): bool {
					return Str::startsWith($value, '#') === true;
				},
			],
			'email' => [
				'detect' => function (string $value): bool {
					return Str::startsWith($value, 'mailto:') === true;
				},
				'link' => function (string $value): string {
					return str_replace('mailto:', '', $value);
				},
				'text' => function (string $value): string {
					return str_replace('mailto:', '', $value);
				},
				'validate' => function (string $value): bool {
					return V::email($value);
				},
			],
			'file' => [
				'detect' => function (string $value): bool {
					return Str::startsWith($value, 'file://') === true;
				},
				'link' => function (string $value): string {
					return $value;
				},
				'text' => function (string $value) use ($kirby): string {
					return $kirby->file($value)?->filename();
				},
				'validate' => function (string $value): bool {
					return V::uuid($value, 'file');
				},
			],
			'page' => [
				'detect' => function (string $value): bool {
					return Str::startsWith($value, 'page://') === true;
				},
				'link' => function (string $value): string {
					return $value;
				},
				'text' => function (string $value) use ($kirby): string {
					return $kirby->page($value)?->title()->value();
				},
				'validate' => function (string $value): bool {
					return V::uuid($value, 'page');
				},
			],
			'tel' => [
				'detect' => function (string $value): bool {
					return Str::startsWith($value, 'tel:') === true;
				},
				'link' => function (string $value): string {
					return str_replace('tel:', '', $value);
				},
				'text' => function (string $value): string {
					return str_replace('tel:', '', $value);
				},
				'validate' => function (string $value): bool {
					return V::tel($value);
				},
			],
			'url' => [
				'detect' => function (string $value): bool {
					return Str::startsWith($value, 'http://') === true || Str::startsWith($value, 'https://') === true;
				},
				'link' => function (string $value): string {
					return $value;
				},
				'text' => function (string $value): string {
					return $value;
				},
				'validate' => function (string $value): bool {
					return V::url($value);
				},
			],

			// needs to come last
			'custom' => [
				'detect' => function (string $value): bool {
					return true;
				},
				'link' => function (string $value): string {
					return $value;
				},
				'text' => function (string $value): string {
					return $value;
				},
				'validate' => function (): bool {
					return true;
				},
			]
		];
	}
}
