<?php

use Kirby\Cms\App;
use Kirby\Cms\Helpers;
use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Http\Router;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

if (Helpers::hasOverride('asset') === false) { // @codeCoverageIgnore
	/**
	 * Helper to create an asset object
	 *
	 * @param string $path
	 * @return \Kirby\Filesystem\Asset
	 */
	function asset(string $path)
	{
		return new Asset($path);
	}
}

if (Helpers::hasOverride('attr') === false) { // @codeCoverageIgnore
	/**
	 * Generates a list of HTML attributes
	 *
	 * @param array|null $attr A list of attributes as key/value array
	 * @param string|null $before An optional string that will be prepended if the result is not empty
	 * @param string|null $after An optional string that will be appended if the result is not empty
	 * @return string|null
	 */
	function attr(array|null $attr = null, string|null $before = null, string|null $after = null): string|null
	{
		return Html::attr($attr, null, $before, $after);
	}
}

if (Helpers::hasOverride('collection') === false) { // @codeCoverageIgnore
	/**
	 * Returns the result of a collection by name
	 *
	 * @param string $name
	 * @return \Kirby\Cms\Collection|null
	 */
	function collection(string $name)
	{
		return App::instance()->collection($name);
	}
}

if (Helpers::hasOverride('csrf') === false) { // @codeCoverageIgnore
	/**
	 * Checks / returns a CSRF token
	 *
	 * @param string|null $check Pass a token here to compare it to the one in the session
	 * @return string|bool Either the token or a boolean check result
	 */
	function csrf(string|null $check = null)
	{
		// check explicitly if there have been no arguments at all;
		// checking for null introduces a security issue because null could come
		// from user input or bugs in the calling code!
		if (func_num_args() === 0) {
			return App::instance()->csrf();
		}

		return App::instance()->csrf($check);
	}
}

if (Helpers::hasOverride('css') === false) { // @codeCoverageIgnore
	/**
	 * Creates one or multiple CSS link tags
	 *
	 * @param string|array $url Relative or absolute URLs, an array of URLs or `@auto` for automatic template css loading
	 * @param string|array $options Pass an array of attributes for the link tag or a media attribute string
	 * @return string|null
	 */
	function css($url, $options = null): string|null
	{
		return Html::css($url, $options);
	}
}

if (Helpers::hasOverride('deprecated') === false) { // @codeCoverageIgnore
	/**
	 * Triggers a deprecation warning if debug mode is active
	 * @since 3.3.0
	 *
	 * @param string $message
	 * @return bool Whether the warning was triggered
	 */
	function deprecated(string $message): bool
	{
		return Helpers::deprecated($message);
	}
}

if (Helpers::hasOverride('dump') === false) { // @codeCoverageIgnore
	/**
	 * Simple object and variable dumper
	 * to help with debugging.
	 *
	 * @param mixed $variable
	 * @param bool $echo
	 * @return string
	 */
	function dump($variable, bool $echo = true): string
	{
		return Helpers::dump($variable, $echo);
	}
}

if (Helpers::hasOverride('e') === false) { // @codeCoverageIgnore
	/**
	 * Smart version of echo with an if condition as first argument
	 *
	 * @param mixed $condition
	 * @param mixed $value The string to be echoed if the condition is true
	 * @param mixed $alternative An alternative string which should be echoed when the condition is false
	 */
	function e($condition, $value, $alternative = null)
	{
		echo $condition ? $value : $alternative;
	}
}

if (Helpers::hasOverride('esc') === false) { // @codeCoverageIgnore
	/**
	 * Escape context specific output
	 *
	 * @param string $string Untrusted data
	 * @param string $context Location of output (`html`, `attr`, `js`, `css`, `url` or `xml`)
	 * @return string Escaped data
	 */
	function esc(string $string, string $context = 'html'): string
	{
		return Str::esc($string, $context);
	}
}

if (Helpers::hasOverride('get') === false) { // @codeCoverageIgnore
	/**
	 * Shortcut for $kirby->request()->get()
	 *
	 * @param mixed $key The key to look for. Pass false or null to return the entire request array.
	 * @param mixed $default Optional default value, which should be returned if no element has been found
	 * @return mixed
	 */
	function get($key = null, $default = null)
	{
		return App::instance()->request()->get($key, $default);
	}
}

if (Helpers::hasOverride('gist') === false) { // @codeCoverageIgnore
	/**
	 * Embeds a Github Gist
	 *
	 * @param string $url
	 * @param string|null $file
	 * @return string
	 */
	function gist(string $url, string|null $file = null): string
	{
		return App::instance()->kirbytag([
			'gist' => $url,
			'file' => $file,
		]);
	}
}

if (Helpers::hasOverride('go') === false) { // @codeCoverageIgnore
	/**
	 * Redirects to the given Urls
	 * Urls can be relative or absolute.
	 *
	 * @param string $url
	 * @param int $code
	 * @return void
	 */
	function go(string $url = '/', int $code = 302)
	{
		Response::go($url, $code);
	}
}

if (Helpers::hasOverride('h') === false) { // @codeCoverageIgnore
	/**
	 * Shortcut for html()
	 *
	 * @param string|null $string unencoded text
	 * @param bool $keepTags
	 * @return string
	 */
	function h(string|null $string, bool $keepTags = false): string
	{
		return Html::encode($string, $keepTags);
	}
}

if (Helpers::hasOverride('html') === false) { // @codeCoverageIgnore
	/**
	 * Creates safe html by encoding special characters
	 *
	 * @param string|null $string unencoded text
	 * @param bool $keepTags
	 * @return string
	 */
	function html(string|null $string, bool $keepTags = false): string
	{
		return Html::encode($string, $keepTags);
	}
}

if (Helpers::hasOverride('image') === false) { // @codeCoverageIgnore
	/**
	 * Return an image from any page
	 * specified by the path
	 *
	 * Example:
	 * <?= image('some/page/myimage.jpg') ?>
	 *
	 * @param string|null $path
	 * @return \Kirby\Cms\File|null
	 */
	function image(string|null $path = null)
	{
		return App::instance()->image($path);
	}
}

if (Helpers::hasOverride('invalid') === false) { // @codeCoverageIgnore
	/**
	 * Runs a number of validators on a set of data and checks if the data is invalid
	 *
	 * @param array $data
	 * @param array $rules
	 * @param array $messages
	 * @return array
	 */
	function invalid(array $data = [], array $rules = [], array $messages = []): array
	{
		return V::invalid($data, $rules, $messages);
	}
}

if (Helpers::hasOverride('js') === false) { // @codeCoverageIgnore
	/**
	 * Creates a script tag to load a javascript file
	 *
	 * @param string|array $url
	 * @param string|array $options
	 * @return string|null
	 */
	function js($url, $options = null): string|null
	{
		return Html::js($url, $options);
	}
}

if (Helpers::hasOverride('kirby') === false) { // @codeCoverageIgnore
	/**
	 * Returns the Kirby object in any situation
	 *
	 * @return \Kirby\Cms\App
	 */
	function kirby()
	{
		return App::instance();
	}
}

if (Helpers::hasOverride('kirbytag') === false) { // @codeCoverageIgnore
	/**
	 * Makes it possible to use any defined Kirbytag as standalone function
	 *
	 * @param string|array $type
	 * @param string|null $value
	 * @param array $attr
	 * @param array $data
	 * @return string
	 */
	function kirbytag($type, string|null $value = null, array $attr = [], array $data = []): string
	{
		return App::instance()->kirbytag($type, $value, $attr, $data);
	}
}

if (Helpers::hasOverride('kirbytags') === false) { // @codeCoverageIgnore
	/**
	 * Parses KirbyTags in the given string. Shortcut
	 * for `$kirby->kirbytags($text, $data)`
	 *
	 * @param string|null $text
	 * @param array $data
	 * @return string
	 */
	function kirbytags(string|null $text = null, array $data = []): string
	{
		return App::instance()->kirbytags($text, $data);
	}
}

if (Helpers::hasOverride('kirbytext') === false) { // @codeCoverageIgnore
	/**
	 * Parses KirbyTags and Markdown in the
	 * given string. Shortcut for `$kirby->kirbytext()`
	 *
	 * @param string|null $text
	 * @param array $data
	 * @return string
	 */
	function kirbytext(string|null $text = null, array $data = []): string
	{
		return App::instance()->kirbytext($text, $data);
	}
}

if (Helpers::hasOverride('kirbytextinline') === false) { // @codeCoverageIgnore
	/**
	 * Parses KirbyTags and inline Markdown in the
	 * given string.
	 * @since 3.1.0
	 *
	 * @param string|null $text
	 * @param array $options
	 * @return string
	 */
	function kirbytextinline(string|null $text = null, array $options = []): string
	{
		$options['markdown']['inline'] = true;
		return App::instance()->kirbytext($text, $options);
	}
}

if (Helpers::hasOverride('kt') === false) { // @codeCoverageIgnore
	/**
	 * Shortcut for `kirbytext()` helper
	 *
	 * @param string|null $text
	 * @param array $data
	 * @return string
	 */
	function kt(string|null $text = null, array $data = []): string
	{
		return App::instance()->kirbytext($text, $data);
	}
}

if (Helpers::hasOverride('kti') === false) { // @codeCoverageIgnore
	/**
	 * Shortcut for `kirbytextinline()` helper
	 * @since 3.1.0
	 *
	 * @param string|null $text
	 * @param array $options
	 * @return string
	 */
	function kti(string|null $text = null, array $options = []): string
	{
		$options['markdown']['inline'] = true;
		return App::instance()->kirbytext($text, $options);
	}
}

if (Helpers::hasOverride('load') === false) { // @codeCoverageIgnore
	/**
	 * A super simple class autoloader
	 *
	 * @param array $classmap
	 * @param string|null $base
	 * @return void
	 */
	function load(array $classmap, string|null $base = null): void
	{
		F::loadClasses($classmap, $base);
	}
}

if (Helpers::hasOverride('markdown') === false) { // @codeCoverageIgnore
	/**
	 * Parses markdown in the given string. Shortcut for
	 * `$kirby->markdown($text)`
	 *
	 * @param string|null $text
	 * @param array $options
	 * @return string
	 */
	function markdown(string|null $text = null, array $options = []): string
	{
		return App::instance()->markdown($text, $options);
	}
}

if (Helpers::hasOverride('option') === false) { // @codeCoverageIgnore
	/**
	 * Shortcut for `$kirby->option($key, $default)`
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function option(string $key, $default = null)
	{
		return App::instance()->option($key, $default);
	}
}

if (Helpers::hasOverride('page') === false) { // @codeCoverageIgnore
	/**
	 * Fetches a single page by id or
	 * the current page when no id is specified
	 *
	 * @param string|null $id
	 * @return \Kirby\Cms\Page|null
	 */
	function page(string|null $id = null)
	{
		if (empty($id) === true) {
			return App::instance()->site()->page();
		}

		return App::instance()->site()->find($id);
	}
}

if (Helpers::hasOverride('pages') === false) { // @codeCoverageIgnore
	/**
	 * Helper to build pages collection
	 *
	 * @param string|array ...$id
	 * @return \Kirby\Cms\Pages|null
	 */
	function pages(...$id)
	{
		// ensure that a list of string arguments and an array
		// as the first argument are treated the same
		if (count($id) === 1 && is_array($id[0]) === true) {
			$id = $id[0];
		}

		// always passes $id an array; ensures we get a
		// collection even if only one ID is passed
		return App::instance()->site()->find($id);
	}
}

if (Helpers::hasOverride('param') === false) { // @codeCoverageIgnore
	/**
	 * Returns a single param from the URL
	 *
	 * @param string $key
	 * @param string|null $fallback
	 * @return string|null
	 * @psalm-return ($fallback is string ? string : string|null)
	 */
	function param(string $key, string|null $fallback = null): string|null
	{
		return App::instance()->request()->url()->params()->$key ?? $fallback;
	}
}

if (Helpers::hasOverride('params') === false) { // @codeCoverageIgnore
	/**
	 * Returns all params from the current Url
	 *
	 * @return array
	 */
	function params(): array
	{
		return App::instance()->request()->url()->params()->toArray();
	}
}

if (Helpers::hasOverride('r') === false) { // @codeCoverageIgnore
	/**
	 * Smart version of return with an if condition as first argument
	 *
	 * @param mixed $condition
	 * @param mixed $value The string to be returned if the condition is true
	 * @param mixed $alternative An alternative string which should be returned when the condition is false
	 * @return mixed
	 */
	function r($condition, $value, $alternative = null)
	{
		return $condition ? $value : $alternative;
	}
}

if (Helpers::hasOverride('router') === false) { // @codeCoverageIgnore
	/**
	 * Creates a micro-router and executes
	 * the routing action immediately
	 * @since 3.6.0
	 *
	 * @param string|null $path
	 * @param string $method
	 * @param array $routes
	 * @param \Closure|null $callback
	 * @return mixed
	 */
	function router(string|null $path = null, string $method = 'GET', array $routes = [], Closure|null $callback = null)
	{
		return Router::execute($path, $method, $routes, $callback);
	}
}

if (Helpers::hasOverride('site') === false) { // @codeCoverageIgnore
	/**
	 * Returns the current site object
	 *
	 * @return \Kirby\Cms\Site
	 */
	function site()
	{
		return App::instance()->site();
	}
}

if (Helpers::hasOverride('size') === false) { // @codeCoverageIgnore
	/**
	 * Determines the size/length of numbers, strings, arrays and countable objects
	 *
	 * @param mixed $value
	 * @return int
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	function size($value): int
	{
		return Helpers::size($value);
	}
}

if (Helpers::hasOverride('smartypants') === false) { // @codeCoverageIgnore
	/**
	 * Enhances the given string with
	 * smartypants. Shortcut for `$kirby->smartypants($text)`
	 *
	 * @param string|null $text
	 * @return string
	 */
	function smartypants(string|null $text = null): string
	{
		return App::instance()->smartypants($text);
	}
}

if (Helpers::hasOverride('snippet') === false) { // @codeCoverageIgnore
	/**
	 * Embeds a snippet from the snippet folder
	 *
	 * @param string|array $name
	 * @param array|object $data
	 * @param bool $return
	 * @return string|null
	 */
	function snippet($name, $data = [], bool $return = false): string|null
	{
		return App::instance()->snippet($name, $data, $return);
	}
}

if (Helpers::hasOverride('svg') === false) { // @codeCoverageIgnore
	/**
	 * Includes an SVG file by absolute or
	 * relative file path.
	 *
	 * @param string|\Kirby\Cms\File $file
	 * @return string|false
	 */
	function svg($file)
	{
		return Html::svg($file);
	}
}

if (Helpers::hasOverride('t') === false) { // @codeCoverageIgnore
	/**
	 * Returns translate string for key from translation file
	 *
	 * @param string|array $key
	 * @param string|null $fallback
	 * @param string|null $locale
	 * @return array|string|null
	 */
	function t($key, string $fallback = null, string $locale = null)
	{
		return I18n::translate($key, $fallback, $locale);
	}
}

if (Helpers::hasOverride('tc') === false) { // @codeCoverageIgnore
	/**
	 * Translates a count
	 *
	 * @param string $key
	 * @param int $count
	 * @param string|null $locale
	 * @param bool $formatNumber If set to `false`, the count is not formatted
	 * @return mixed
	 */
	function tc(
		string $key,
		int $count,
		string $locale = null,
		bool $formatNumber = true
	) {
		return I18n::translateCount($key, $count, $locale, $formatNumber);
	}
}

if (Helpers::hasOverride('timestamp') === false) { // @codeCoverageIgnore
	/**
	 * Rounds the minutes of the given date
	 * by the defined step
	 *
	 * @param string|null $date
	 * @param int|array|null $step array of `unit` and `size` to round to nearest
	 * @return int|null
	 */
	function timestamp(string|null $date = null, $step = null): int|null
	{
		return Date::roundedTimestamp($date, $step);
	}
}

if (Helpers::hasOverride('tt') === false) { // @codeCoverageIgnore
	/**
	 * Translate by key and then replace
	 * placeholders in the text
	 *
	 * @param string $key
	 * @param string|array|null $fallback
	 * @param array|null $replace
	 * @param string|null $locale
	 * @return string
	 */
	function tt(string $key, $fallback = null, array|null $replace = null, string|null $locale = null): string
	{
		return I18n::template($key, $fallback, $replace, $locale);
	}
}

if (Helpers::hasOverride('twitter') === false) { // @codeCoverageIgnore
	/**
	 * Builds a Twitter link
	 *
	 * @param string $username
	 * @param string|null $text
	 * @param string|null $title
	 * @param string|null $class
	 * @return string
	 */
	function twitter(string $username, string|null $text = null, string|null $title = null, string|null $class = null): string
	{
		return App::instance()->kirbytag([
			'twitter' => $username,
			'text'    => $text,
			'title'   => $title,
			'class'   => $class
		]);
	}
}

if (Helpers::hasOverride('u') === false) { // @codeCoverageIgnore
	/**
	 * Shortcut for url()
	 *
	 * @param string|null $path
	 * @param array|string|null $options
	 * @return string
	 */
	function u(string|null $path = null, $options = null): string
	{
		return Url::to($path, $options);
	}
}

if (Helpers::hasOverride('url') === false) { // @codeCoverageIgnore
	/**
	 * Builds an absolute URL for a given path
	 *
	 * @param string|null $path
	 * @param array|string|null $options
	 * @return string
	 */
	function url(string|null $path = null, $options = null): string
	{
		return Url::to($path, $options);
	}
}

if (Helpers::hasOverride('uuid') === false) { // @codeCoverageIgnore
	/**
	 * Creates a compliant v4 UUID
	 *
	 * @return string
	 */
	function uuid(): string
	{
		return Str::uuid();
	}
}

if (Helpers::hasOverride('video') === false) { // @codeCoverageIgnore
	/**
	 * Creates a video embed via iframe for Youtube or Vimeo
	 * videos. The embed Urls are automatically detected from
	 * the given Url.
	 *
	 * @param string $url
	 * @param array $options
	 * @param array $attr
	 * @return string|null
	 */
	function video(string $url, array $options = [], array $attr = []): string|null
	{
		return Html::video($url, $options, $attr);
	}
}

if (Helpers::hasOverride('vimeo') === false) { // @codeCoverageIgnore
	/**
	 * Embeds a Vimeo video by URL in an iframe
	 *
	 * @param string $url
	 * @param array $options
	 * @param array $attr
	 * @return string|null
	 */
	function vimeo(string $url, array $options = [], array $attr = []): string|null
	{
		return Html::vimeo($url, $options, $attr);
	}
}

if (Helpers::hasOverride('widont') === false) { // @codeCoverageIgnore
	/**
	 * The widont function makes sure that there are no
	 * typographical widows at the end of a paragraph –
	 * that's a single word in the last line
	 *
	 * @param string|null $string
	 * @return string
	 */
	function widont(string $string = null): string
	{
		return Str::widont($string);
	}
}

if (Helpers::hasOverride('youtube') === false) { // @codeCoverageIgnore
	/**
	 * Embeds a Youtube video by URL in an iframe
	 *
	 * @param string $url
	 * @param array $options
	 * @param array $attr
	 * @return string|null
	 */
	function youtube(string $url, array $options = [], array $attr = []): string|null
	{
		return Html::youtube($url, $options, $attr);
	}
}
