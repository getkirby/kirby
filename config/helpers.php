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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_ASSET') === false ||
    constant('KIRBY_HELPER_ASSET') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_ATTR') === false ||
    constant('KIRBY_HELPER_ATTR') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Generates a list of HTML attributes
     *
     * @param array|null $attr A list of attributes as key/value array
     * @param string|null $before An optional string that will be prepended if the result is not empty
     * @param string|null $after An optional string that will be appended if the result is not empty
     * @return string|null
     */
    function attr(?array $attr = null, ?string $before = null, ?string $after = null): ?string
    {
        return Html::attr($attr, null, $before, $after);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_COLLECTION') === false ||
    constant('KIRBY_HELPER_COLLECTION') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_CSRF') === false ||
    constant('KIRBY_HELPER_CSRF') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Checks / returns a CSRF token
     *
     * @param string|null $check Pass a token here to compare it to the one in the session
     * @return string|bool Either the token or a boolean check result
     */
    function csrf(?string $check = null)
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_CSS') === false ||
    constant('KIRBY_HELPER_CSS') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Creates one or multiple CSS link tags
     *
     * @param string|array $url Relative or absolute URLs, an array of URLs or `@auto` for automatic template css loading
     * @param string|array $options Pass an array of attributes for the link tag or a media attribute string
     * @return string|null
     */
    function css($url, $options = null): ?string
    {
        return Html::css($url, $options);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_DEPRECATED') === false ||
    constant('KIRBY_HELPER_DEPRECATED') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_DUMP') === false ||
    constant('KIRBY_HELPER_DUMP') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_E') === false ||
    constant('KIRBY_HELPER_E') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_ESC') === false ||
    constant('KIRBY_HELPER_ESC') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_GET') === false ||
    constant('KIRBY_HELPER_GET') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_GIST') === false ||
    constant('KIRBY_HELPER_GIST') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Embeds a Github Gist
     *
     * @param string $url
     * @param string|null $file
     * @return string
     */
    function gist(string $url, ?string $file = null): string
    {
        return App::instance()->kirbytag([
            'gist' => $url,
            'file' => $file,
        ]);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_GO') === false ||
    constant('KIRBY_HELPER_GO') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_H') === false ||
    constant('KIRBY_HELPER_H') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Shortcut for html()
     *
     * @param string|null $string unencoded text
     * @param bool $keepTags
     * @return string
     */
    function h(?string $string, bool $keepTags = false)
    {
        return Html::encode($string, $keepTags);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_HTML') === false ||
    constant('KIRBY_HELPER_HTML') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Creates safe html by encoding special characters
     *
     * @param string|null $string unencoded text
     * @param bool $keepTags
     * @return string
     */
    function html(?string $string, bool $keepTags = false)
    {
        return Html::encode($string, $keepTags);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_IMAGE') === false ||
    constant('KIRBY_HELPER_IMAGE') !== false
) { // @codeCoverageIgnoreEnd
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
    function image(?string $path = null)
    {
        return App::instance()->image($path);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_INVALID') === false ||
    constant('KIRBY_HELPER_INVALID') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_JS') === false ||
    constant('KIRBY_HELPER_JS') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Creates a script tag to load a javascript file
     *
     * @param string|array $url
     * @param string|array $options
     * @return string|null
     */
    function js($url, $options = null): ?string
    {
        return Html::js($url, $options);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_KIRBY') === false ||
    constant('KIRBY_HELPER_KIRBY') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_KIRBYTAG') === false ||
    constant('KIRBY_HELPER_KIRBYTAG') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Makes it possible to use any defined Kirbytag as standalone function
     *
     * @param string|array $type
     * @param string|null $value
     * @param array $attr
     * @param array $data
     * @return string
     */
    function kirbytag($type, ?string $value = null, array $attr = [], array $data = []): string
    {
        return App::instance()->kirbytag($type, $value, $attr, $data);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_KIRBYTAGS') === false ||
    constant('KIRBY_HELPER_KIRBYTAGS') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Parses KirbyTags in the given string. Shortcut
     * for `$kirby->kirbytags($text, $data)`
     *
     * @param string|null $text
     * @param array $data
     * @return string
     */
    function kirbytags(?string $text = null, array $data = []): string
    {
        return App::instance()->kirbytags($text, $data);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_KIRBYTEXT') === false ||
    constant('KIRBY_HELPER_KIRBYTEXT') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Parses KirbyTags and Markdown in the
     * given string. Shortcut for `$kirby->kirbytext()`
     *
     * @param string|null $text
     * @param array $data
     * @return string
     */
    function kirbytext(?string $text = null, array $data = []): string
    {
        return App::instance()->kirbytext($text, $data);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_KIRBYTEXTINLINE') === false ||
    constant('KIRBY_HELPER_KIRBYTEXTINLINE') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Parses KirbyTags and inline Markdown in the
     * given string.
     * @since 3.1.0
     *
     * @param string|null $text
     * @param array $data
     * @return string
     */
    function kirbytextinline(?string $text = null, array $data = []): string
    {
        return App::instance()->kirbytext($text, $data, true);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_KT') === false ||
    constant('KIRBY_HELPER_KT') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Shortcut for `kirbytext()` helper
     *
     * @param string|null $text
     * @param array $data
     * @return string
     */
    function kt(?string $text = null, array $data = []): string
    {
        return App::instance()->kirbytext($text, $data);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_KTI') === false ||
    constant('KIRBY_HELPER_KTI') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Shortcut for `kirbytextinline()` helper
     * @since 3.1.0
     *
     * @param string|null $text
     * @param array $data
     * @return string
     */
    function kti(?string $text = null, array $data = []): string
    {
        return App::instance()->kirbytext($text, $data, true);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_LOAD') === false ||
    constant('KIRBY_HELPER_LOAD') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * A super simple class autoloader
     *
     * @param array $classmap
     * @param string|null $base
     * @return void
     */
    function load(array $classmap, ?string $base = null)
    {
        return F::loadClasses($classmap, $base);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_MARKDOWN') === false ||
    constant('KIRBY_HELPER_MARKDOWN') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Parses markdown in the given string. Shortcut for
     * `$kirby->markdown($text)`
     *
     * @param array $data
     * @return string
     */
    function markdown(?string $text = null, array $data = []): string
    {
        return App::instance()->markdown($text, $data);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_OPTION') === false ||
    constant('KIRBY_HELPER_OPTION') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_PAGE') === false ||
    constant('KIRBY_HELPER_PAGE') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Fetches a single page or multiple pages by
     * id or the current page when no id is specified
     *
     * @param string|array ...$id
     * @return \Kirby\Cms\Page|\Kirby\Cms\Pages|null
     * @todo reduce to one parameter in 3.7.0 (also change return and return type)
     */
    function page(...$id)
    {
        if (empty($id) === true) {
            return App::instance()->site()->page();
        }

        if (count($id) > 1) {
            // @codeCoverageIgnoreStart
            Helpers::deprecated('Passing multiple parameters to the `page()` helper has been deprecated. Please use the `pages()` helper instead.');
            // @codeCoverageIgnoreEnd
        }

        return App::instance()->site()->find(...$id);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_PAGES') === false ||
    constant('KIRBY_HELPER_PAGES') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Helper to build page collections
     *
     * @param string|array ...$id
     * @return \Kirby\Cms\Page|\Kirby\Cms\Pages|null
     * @todo return only Pages|null in 3.7.0, wrap in Pages for single passed id
     */
    function pages(...$id)
    {
        if (count($id) === 1 && is_array($id[0]) === false) {
            // @codeCoverageIgnoreStart
            Helpers::deprecated('Passing a single id to the `pages()` helper will return a Kirby\Cms\Pages collection with a single element instead of the single Kirby\Cms\Page object itself - starting in 3.7.0.');
            // @codeCoverageIgnoreEnd
        }

        return App::instance()->site()->find(...$id);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_PARAM') === false ||
    constant('KIRBY_HELPER_PARAM') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Returns a single param from the URL
     *
     * @param string $key
     * @param string|null $fallback
     * @return string|null
     */
    function param(string $key, ?string $fallback = null): ?string
    {
        return App::instance()->request()->url()->params()->$key ?? $fallback;
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_PARAMS') === false ||
    constant('KIRBY_HELPER_PARAMS') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_R') === false ||
    constant('KIRBY_HELPER_R') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_ROUTER') === false ||
    constant('KIRBY_HELPER_ROUTER') !== false
) { // @codeCoverageIgnoreEnd
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
    function router(?string $path = null, string $method = 'GET', array $routes = [], ?Closure $callback = null)
    {
        return Router::execute($path, $method, $routes, $callback);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_SITE') === false ||
    constant('KIRBY_HELPER_SITE') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_SIZE') === false ||
    constant('KIRBY_HELPER_SIZE') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_SMARTYPANTS') === false ||
    constant('KIRBY_HELPER_SMARTYPANTS') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Enhances the given string with
     * smartypants. Shortcut for `$kirby->smartypants($text)`
     *
     * @param string|null $text
     * @return string
     */
    function smartypants(?string $text = null): string
    {
        return App::instance()->smartypants($text);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_SNIPPET') === false ||
    constant('KIRBY_HELPER_SNIPPET') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Embeds a snippet from the snippet folder
     *
     * @param string|array $name
     * @param array|object $data
     * @param bool $return
     * @return string
     */
    function snippet($name, $data = [], bool $return = false)
    {
        return App::instance()->snippet($name, $data, $return);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_SVG') === false ||
    constant('KIRBY_HELPER_SVG') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_T') === false ||
    constant('KIRBY_HELPER_T') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_TC') === false ||
    constant('KIRBY_HELPER_TC') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_TIMESTAMP') === false ||
    constant('KIRBY_HELPER_TIMESTAMP') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Rounds the minutes of the given date
     * by the defined step
     *
     * @param string|null $date
     * @param int|array|null $step array of `unit` and `size` to round to nearest
     * @return int|null
     */
    function timestamp(?string $date = null, $step = null): ?int
    {
        return Date::roundedTimestamp($date, $step);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_TT') === false ||
    constant('KIRBY_HELPER_TT') !== false
) { // @codeCoverageIgnoreEnd
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
    function tt(string $key, $fallback = null, ?array $replace = null, ?string $locale = null)
    {
        return I18n::template($key, $fallback, $replace, $locale);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_TWITTER') === false ||
    constant('KIRBY_HELPER_TWITTER') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Builds a Twitter link
     *
     * @param string $username
     * @param string|null $text
     * @param string|null $title
     * @param string|null $class
     * @return string
     */
    function twitter(string $username, ?string $text = null, ?string $title = null, ?string $class = null): string
    {
        return App::instance()->kirbytag([
            'twitter' => $username,
            'text'    => $text,
            'title'   => $title,
            'class'   => $class
        ]);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_U') === false ||
    constant('KIRBY_HELPER_U') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Shortcut for url()
     *
     * @param string|null $path
     * @param array|string|null $options
     * @return string
     */
    function u(?string $path = null, $options = null): string
    {
        return Url::to($path, $options);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_URL') === false ||
    constant('KIRBY_HELPER_URL') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Builds an absolute URL for a given path
     *
     * @param string|null $path
     * @param array|string|null $options
     * @return string
     */
    function url(?string $path = null, $options = null): string
    {
        return Url::to($path, $options);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_UUID') === false ||
    constant('KIRBY_HELPER_UUID') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_VIDEO') === false ||
    constant('KIRBY_HELPER_VIDEO') !== false
) { // @codeCoverageIgnoreEnd
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
    function video(string $url, array $options = [], array $attr = []): ?string
    {
        return Html::video($url, $options, $attr);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_VIMEO') === false ||
    constant('KIRBY_HELPER_VIMEO') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Embeds a Vimeo video by URL in an iframe
     *
     * @param string $url
     * @param array $options
     * @param array $attr
     * @return string|null
     */
    function vimeo(string $url, array $options = [], array $attr = []): ?string
    {
        return Html::vimeo($url, $options, $attr);
    }
}

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_WIDONT') === false ||
    constant('KIRBY_HELPER_WIDONT') !== false
) { // @codeCoverageIgnoreEnd
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

// @codeCoverageIgnoreStart
if (
    defined('KIRBY_HELPER_YOUTUBE') === false ||
    constant('KIRBY_HELPER_YOUTUBE') !== false
) { // @codeCoverageIgnoreEnd
    /**
     * Embeds a Youtube video by URL in an iframe
     *
     * @param string $url
     * @param array $options
     * @param array $attr
     * @return string|null
     */
    function youtube(string $url, array $options = [], array $attr = []): ?string
    {
        return Html::youtube($url, $options, $attr);
    }
}
