<?php

use Kirby\Cms\App;
use Kirby\Cms\Html;
use Kirby\Cms\Response;
use Kirby\Cms\Url;
use Kirby\Http\Server;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\View;

/**
 * Generates a list of HTML attributes
 *
 * @param array $attr A list of attributes as key/value array
 * @param string $before An optional string that will be prepended if the result is not empty
 * @param string $after An optional string that will be appended if the result is not empty
 * @return string
 */
function attr(array $attr = null, $before = null, $after = null)
{
    if ($attrs = Html::attr($attr)) {
        return $before . $attrs . $after;
    }

    return null;
}

/**
 * Returns the result of a collection by name
 *
 * @param string $name
 * @return Collection|null
 */
function collection(string $name)
{
    return App::instance()->collection($name);
}

/**
 * Creates one or multiple CSS link tags
 *
 * @param string|array $url Relative or absolute URLs, an array of URLs or `@auto` for automatic template css loading
 * @param string|array $options Pass an array of attributes for the link tag or a media attribute string
 * @return string|null
 */
function css($url, $options = null)
{
    if (is_array($url) === true) {
        $links = array_map(function ($url) use ($options) {
            return css($url, $options);
        }, $url);

        return implode(PHP_EOL, $links);
    }

    $href = $url === '@auto' ? Url::toTemplateAsset('css/templates', 'css') : Url::to($url);

    $attr = [
        'href' => $href,
        'rel'  => 'stylesheet'
    ];

    if (is_string($options) === true) {
        $attr['media'] = $options;
    }

    if (is_array($options) === true) {
        $attr = array_merge($options, $attr);
    }

    return '<link ' . attr($attr) . '>';
}

/**
 * Simple object and variable dumper
 * to help with debugging.
 *
 * @param mixed $variable
 * @param boolean $echo
 * @return string
 */
function dump($variable, bool $echo = true): string
{
    if (Server::cli() === true) {
        $output = print_r($variable, true) . PHP_EOL;
    } else {
        $output = '<pre>' . print_r($variable, true) . '</pre>';
    }

    if ($echo === true) {
        echo $output;
    }

    return $output;
}

/**
 * Smart version of echo with an if condition as first argument
 *
 * @param mixed $condition
 * @param mixed $value The string to be echoed if the condition is true
 * @param mixed $alternative An alternative string which should be echoed when the condition is false
 */
function e($condition, $value, $alternative = null)
{
    echo r($condition, $value, $alternative);
}

/**
 * Shortcut for $kirby->request()->get()
 *
 * @param   mixed    $key The key to look for. Pass false or null to return the entire request array.
 * @param   mixed    $default Optional default value, which should be returned if no element has been found
 * @return  mixed
 */
function get($key, $default = null)
{
    return App::instance()->request()->get($key, $default);
}

/**
 * Embeds a Github Gist
 *
 * @param string $url
 * @param string $file
 * @return string
 */
function gist(string $url, string $file = null): string
{
    return kirbytag([
        'gist' => $url,
        'file' => $file,
    ]);
}

/**
 * Redirects to the given Urls
 * Urls can be relative or absolute.
 *
 * @param string $url
 * @param integer $code
 * @return void
 */
function go(string $url = null, int $code = 301)
{
    die(Response::redirect($url, $code));
}

/**
 * Shortcut for html()
 *
 * @param string $text unencoded text
 * @param bool $keepTags
 * @return string
 */
function h(string $string = null, bool $keepTags = false) {
    return Html::encode($string, $keepTags);
}

/**
 * Creates safe html by encoding special characters
 *
 * @param string $text unencoded text
 * @param bool $keepTags
 * @return string
 */
function html(string $string = null, bool $keepTags = false) {
    return Html::encode($string, $keepTags);
}

/**
 * Return an image from any page
 * specified by the path
 *
 * Example:
 * <?= image('some/page/myimage.jpg') ?>
 *
 * @param string $path
 * @return File|null
 */
function image(string $path = null)
{
    if ($path === null) {
        return page()->image();
    }

    $uri      = dirname($path);
    $filename = basename($path);

    if ($uri === '.') {
        $uri = null;
    }

    $page = $uri === '/' ? site() : page($uri);

    if ($page) {
        return $page->image($filename);
    } else {
        return null;
    }
}

/**
 * Creates a script tag to load a javascript file
 *
 * @param string|array $src
 * @param string|array $options
 * @return void
 */
function js($url, $options = null)
{
    if (is_array($url) === true) {
        $scripts = array_map(function ($url) use ($options) {
            return js($url, $options);
        }, $url);

        return implode(PHP_EOL, $scripts);
    }

    $src  = $url === '@auto' ? Url::toTemplateAsset('js/templates', 'js') : Url::to($url);
    $attr = [
        'src' => $src,
    ];

    if (is_bool($options) === true) {
        $attr['async'] = $options;
    }

    if (is_array($options) === true) {
        $attr = array_merge($options, $attr);
    }

    return '<script ' . attr($attr) . '></script>';
}

/**
 * Returns the Kirby object in any situation
 *
 * @return App
 */
function kirby(): App
{
    return App::instance();
}

/**
 * Makes it possible to use any defined Kirbytag as standalone function
 *
 * @param string|array $type
 * @param string $value
 * @param array $attr
 * @return string
 */
function kirbytag($type, string $value = null, array $attr = []): string
{
    if (is_array($type) === true) {
        return App::instance()->kirbytag(key($type), current($type), $type);
    }

    return App::instance()->kirbytag($type, $value, $attr);
}

/**
 * Parses KirbyTags in the given string. Shortcut
 * for `$kirby->kirbytags($text, $data)`
 *
 * @param string $text
 * @param array $data
 * @return string
 */
function kirbytags(string $text = null, array $data = []): string
{
    return App::instance()->kirbytags($text, $data);
}

/**
 * Parses KirbyTags and Markdown in the
 * given string. Shortcut for `$kirby->kirbytext()`
 *
 * @param string $text
 * @param array $data
 * @return string
 */
function kirbytext(string $text = null, array $data = []): string
{
    return App::instance()->kirbytext($text, $data);
}

/**
 * A super simple class autoloader
 *
 * @param array $classmap
 * @param string $base
 * @return void
 */
function load(array $classmap, string $base = null) {
    spl_autoload_register(function ($class) use ($classmap, $base) {

        $class = strtolower($class);

        if (!isset($classmap[$class])) {
            return false;
        }

        if ($base) {
            include $base . '/' . $classmap[$class];
        } else {
            include $classmap[$class];
        }

    });
}

/**
 * Parses markdown in the given string. Shortcut for
 * `$kirby->markdown($text)`
 *
 * @param string $text
 * @return string
 */
function markdown(string $text = null): string
{
    return App::instance()->markdown($text);
}

/**
 * Shortcut for `$kirby->option($key, $default)`
 *
 * @param string $key
 * @param mixed $default
 * @return void
 */
function option(string $key, $default = null)
{
    return App::instance()->option($key, $default);
}

/**
 * Fetches a single page or multiple pages by
 * id or the current page when no id is specified
 *
 * @param string|array ...$id
 * @return Page|null
 */
function page(...$id)
{
    if (empty($id) === true) {
        return App::instance()->site()->page();
    }

    return App::instance()->site()->find(...$id);
}

/**
 * Helper to build page collections
 *
 * @param string|array ...$id
 * @return Pages
 */
function pages(...$id)
{
    return App::instance()->site()->find(...$id);
}

/**
 * Returns a single param from the URL
 *
 * @param string $key
 * @param string $fallback
 * @return string|null
 */
function param(string $key, string $fallback = null): ?string
{
    return App::instance()->request()->url()->params()->$key ?? $fallback;
}

/**
 * Returns all params from the current Url
 *
 * @return array
 */
function params(): array
{
    return App::instance()->request()->url()->params()->toArray();
}

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

/**
 * Returns the currrent site object
 *
 * @return Site
 */
function site()
{
    return App::instance()->site();
}

/**
 * Determines the size/length of numbers, strings, arrays and countable objects
 *
 * @param mixed $value
 * @return int
 */
function size($value): int
{
    if (is_numeric($value)) {
        return $value;
    }

    if (is_string($value)) {
        return Str::length(trim($value));
    }

    if (is_array($value)) {
        return count($value);
    }

    if (is_object($value)) {
        if ($value instanceof Countable) {
            return count($value);
        }
    }
}

/**
 * Enhances the given string with
 * smartypants. Shortcut for `$kirby->smartypants($text)`
 *
 * @param string $text
 * @return string
 */
function smartypants(string $text = null): string
{
    return App::instance()->smartypants($text);
}

/**
 * Embeds a snippet from the snippet folder
 *
 * @param string $name
 * @param array|object $data
 * @param boolean $return
 * @return string
 */
function snippet(string $name, $data = [], bool $return = false)
{
    if (is_object($data) === true) {
        $data = ['item' => $data];
    }

    $snippet = App::instance()->snippet($name, $data);

    if ($return === true) {
        return $snippet;
    }

    echo $snippet;
}

/**
 * Includes an SVG file by absolute or
 * relative file path.
 *
 * @param string $file
 * @return string
 */
function svg(string $file)
{
    $root = App::instance()->root();
    $file = $root . '/' . $file;

    if (file_exists($file) === false) {
        return false;
    }

    ob_start();
    include F::realpath($file, $root);
    $svg = ob_get_contents();
    ob_end_clean();

    return $svg;
}

/**
 * Returns translate string for key from translation file
 *
 * @param   string|array $key
 * @param   string|null  $fallback
 * @return  mixed
 */
function t($key, string $fallback = null)
{
    return I18n::translate($key, $fallback);
}

/**
 * Translates a count
 *
 * @param   string|array $key
 * @param   int  $count
 * @return  mixed
 */
function tc($key, int $count)
{
    return I18n::translateCount($key, $count);
}

/**
 * Builds a Twitter link
 *
 * @param string $username
 * @param string $text
 * @param string $title
 * @param string $class
 * @return string
 */
function twitter(string $username, string $text = null, string $title = null, string $class = null): string
{
    return kirbytag([
        'twitter' => $username,
        'text'    => $text,
        'title'   => $title,
        'class'   => $class
    ]);
}

/**
 * Shortcut for url()
 *
 * @param string $path
 * @param array|null $options
 * @return string
 */
function u(string $path = null, $options = null): string
{
    return Url::to($path, $options);
}

/**
 * Builds an absolute URL for a given path
 *
 * @param string $path
 * @param array $options
 * @return string
 */
function url(string $path = null, $options = null): string
{
    return Url::to($path, $options);
}

/**
 * Creates a video embed via iframe for Youtube or Vimeo
 * videos. The embed Urls are automatically detected from
 * the given Url.
 *
 * @param string $url
 * @param array $options
 * @param array $attr
 * @return string
 */
function video(string $url, array $options = [], array $attr = []): string
{
    return Html::video($url, $options, $attr);
}

/**
 * Embeds a Vimeo video by URL in an iframe
 *
 * @param string $url
 * @param array $options
 * @param array $attr
 * @return string
 */
function vimeo(string $url, array $options = [], array $attr = []): string
{
    return Html::video($url, $options, $attr);
}

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

/**
 * Embeds a Youtube video by URL in an iframe
 *
 * @param string $url
 * @param array $options
 * @param array $attr
 * @return string
 */
function youtube(string $url, array $options = [], array $attr = []): string
{
    return Html::video($url, $options, $attr);
}
