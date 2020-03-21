<?php

use Kirby\Cms\App;
use Kirby\Cms\Asset;
use Kirby\Cms\Html;
use Kirby\Cms\Response;
use Kirby\Cms\Url;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Helper to create an asset object
 *
 * @param string $path
 * @return \Kirby\Cms\Asset
 */
function asset(string $path)
{
    return new Asset($path);
}

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
 * @return \Kirby\Cms\Collection|null
 */
function collection(string $name)
{
    return App::instance()->collection($name);
}

/**
 * Checks / returns a CSRF token
 *
 * @param string $check Pass a token here to compare it to the one in the session
 * @return string|bool Either the token or a boolean check result
 */
function csrf(string $check = null)
{
    $session = App::instance()->session();

    // check explicitly if there have been no arguments at all;
    // checking for null introduces a security issue because null could come
    // from user input or bugs in the calling code!
    if (func_num_args() === 0) {
        // no arguments, generate/return a token

        $token = $session->get('csrf');
        if (is_string($token) !== true) {
            $token = bin2hex(random_bytes(32));
            $session->set('csrf', $token);
        }

        return $token;
    } elseif (is_string($check) === true && is_string($session->get('csrf')) === true) {
        // argument has been passed, check the token
        return hash_equals($session->get('csrf'), $check) === true;
    }

    return false;
}

/**
 * Creates one or multiple CSS link tags
 *
 * @param string|array $url Relative or absolute URLs, an array of URLs or `@auto` for automatic template css loading
 * @param string|array $options Pass an array of attributes for the link tag or a media attribute string
 * @return string|null
 */
function css($url, $options = null): ?string
{
    if (is_array($url) === true) {
        $links = array_map(function ($url) use ($options) {
            return css($url, $options);
        }, $url);

        return implode(PHP_EOL, $links);
    }

    if (is_string($options) === true) {
        $options = ['media' => $options];
    }

    $kirby = App::instance();

    if ($url === '@auto') {
        if (!$url = Url::toTemplateAsset('css/templates', 'css')) {
            return null;
        }
    }

    $url  = $kirby->component('css')($kirby, $url, $options);
    $url  = Url::to($url);
    $attr = array_merge((array)$options, [
        'href' => $url,
        'rel'  => 'stylesheet'
    ]);

    return '<link ' . attr($attr) . '>';
}

/**
 * Triggers a deprecation warning if debug mode is active
 * @since 3.3.0
 *
 * @param string $message
 * @return bool Whether the warning was triggered
 */
function deprecated(string $message): bool
{
    if (App::instance()->option('debug') === true) {
        return trigger_error($message, E_USER_DEPRECATED) === true;
    }

    return false;
}

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
    $kirby = App::instance();
    return $kirby->component('dump')($kirby, $variable, $echo);
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
 * Escape context specific output
 *
 * @param string $string Untrusted data
 * @param string $context Location of output
 * @param bool $strict Whether to escape an extended set of characters (HTML attributes only)
 * @return string Escaped data
 */
function esc($string, $context = 'html', $strict = false)
{
    if (method_exists('Kirby\Toolkit\Escape', $context) === true) {
        return Escape::$context($string, $strict);
    }

    return $string;
}


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
 * @param int $code
 * @return void
 */
function go(string $url = null, int $code = 302)
{
    die(Response::redirect($url, $code));
}

/**
 * Shortcut for html()
 *
 * @param string $string unencoded text
 * @param bool $keepTags
 * @return string
 */
function h(string $string = null, bool $keepTags = false)
{
    return Html::encode($string, $keepTags);
}

/**
 * Creates safe html by encoding special characters
 *
 * @param string $string unencoded text
 * @param bool $keepTags
 * @return string
 */
function html(string $string = null, bool $keepTags = false)
{
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
 * @return \Kirby\Cms\File|null
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

    switch ($uri) {
        case '/':
            $parent = site();
            break;
        case null:
            $parent = page();
            break;
        default:
            $parent = page($uri);
            break;
    }

    if ($parent) {
        return $parent->image($filename);
    } else {
        return null;
    }
}

/**
 * Runs a number of validators on a set of data and checks if the data is invalid
 *
 * @param array $data
 * @param array $rules
 * @param array $messages
 * @return false|array
 */
function invalid(array $data = [], array $rules = [], array $messages = [])
{
    $errors = [];

    foreach ($rules as $field => $validations) {
        $validationIndex = -1;

        // See: http://php.net/manual/en/types.comparisons.php
        // only false for: null, undefined variable, '', []
        $filled  = isset($data[$field]) && $data[$field] !== '' && $data[$field] !== [];
        $message = $messages[$field] ?? $field;

        // True if there is an error message for each validation method.
        $messageArray = is_array($message);

        foreach ($validations as $method => $options) {
            if (is_numeric($method) === true) {
                $method = $options;
            }

            $validationIndex++;

            if ($method === 'required') {
                if ($filled) {
                    // Field is required and filled.
                    continue;
                }
            } elseif ($filled) {
                if (is_array($options) === false) {
                    $options = [$options];
                }

                array_unshift($options, $data[$field] ?? null);

                if (V::$method(...$options) === true) {
                    // Field is filled and passes validation method.
                    continue;
                }
            } else {
                // If a field is not required and not filled, no validation should be done.
                continue;
            }

            // If no continue was called we have a failed validation.
            if ($messageArray) {
                $errors[$field][] = $message[$validationIndex] ?? $field;
            } else {
                $errors[$field] = $message;
            }
        }
    }

    return $errors;
}

/**
 * Creates a script tag to load a javascript file
 *
 * @param string|array $url
 * @param string|array $options
 * @return string|null
 */
function js($url, $options = null): ?string
{
    if (is_array($url) === true) {
        $scripts = array_map(function ($url) use ($options) {
            return js($url, $options);
        }, $url);

        return implode(PHP_EOL, $scripts);
    }

    if (is_bool($options) === true) {
        $options = ['async' => $options];
    }

    $kirby = App::instance();

    if ($url === '@auto') {
        if (!$url = Url::toTemplateAsset('js/templates', 'js')) {
            return null;
        }
    }

    $url  = $kirby->component('js')($kirby, $url, $options);
    $url  = Url::to($url);
    $attr = array_merge((array)$options, ['src' => $url]);

    return '<script ' . attr($attr) . '></script>';
}

/**
 * Returns the Kirby object in any situation
 *
 * @return \Kirby\Cms\App
 */
function kirby()
{
    return App::instance();
}

/**
 * Makes it possible to use any defined Kirbytag as standalone function
 *
 * @param string|array $type
 * @param string $value
 * @param array $attr
 * @param array $data
 * @return string
 */
function kirbytag($type, string $value = null, array $attr = [], array $data = []): string
{
    if (is_array($type) === true) {
        $kirbytag = $type;
        $type     = key($kirbytag);
        $value    = current($kirbytag);
        $attr     = $kirbytag;

        // check data attribute and separate from attr data if exists
        if (isset($attr['data']) === true) {
            $data = $attr['data'];
            unset($attr['data']);
        }
    }

    return App::instance()->kirbytag($type, $value, $attr, $data);
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
 * Parses KirbyTags and inline Markdown in the
 * given string.
 * @since 3.1.0
 *
 * @param string $text
 * @param array $data
 * @return string
 */
function kirbytextinline(string $text = null, array $data = []): string
{
    return App::instance()->kirbytext($text, $data, true);
}

/**
 * Shortcut for `kirbytext()` helper
 *
 * @param string $text
 * @param array $data
 * @return string
 */
function kt(string $text = null, array $data = []): string
{
    return kirbytext($text, $data);
}

/**
 * Shortcut for `kirbytextinline()` helper
 * @since 3.1.0
 *
 * @param string $text
 * @param array $data
 * @return string
 */
function kti(string $text = null, array $data = []): string
{
    return kirbytextinline($text, $data);
}

/**
 * A super simple class autoloader
 *
 * @param array $classmap
 * @param string $base
 * @return void
 */
function load(array $classmap, string $base = null)
{
    // convert all classnames to lowercase
    $classmap = array_change_key_case($classmap);

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
 * @return mixed
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
 * @return \Kirby\Cms\Page|null
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
 * @return \Kirby\Cms\Pages
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
 * Rounds the minutes of the given date
 * by the defined step
 *
 * @param string $date
 * @param int $step
 * @return string|null
 */
function timestamp(string $date = null, int $step = null): ?string
{
    if (V::date($date) === false) {
        return null;
    }

    $date = strtotime($date);

    if ($step === null) {
        return $date;
    }

    $hours   = date('H', $date);
    $minutes = date('i', $date);
    $minutes = floor($minutes / $step) * $step;
    $minutes = str_pad($minutes, 2, 0, STR_PAD_LEFT);
    $date    = date('Y-m-d', $date) . ' ' . $hours . ':' . $minutes;

    return strtotime($date);
}

/**
 * Returns the currrent site object
 *
 * @return \Kirby\Cms\Site
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
        if (is_a($value, 'Countable') === true) {
            return count($value);
        }

        if (is_a($value, 'Kirby\Toolkit\Collection') === true) {
            return $value->count();
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
 * @param string|array $name
 * @param array|object $data
 * @param bool $return
 * @return string
 */
function snippet($name, $data = [], bool $return = false)
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
 * @param string|\Kirby\Cms\File $file
 * @return string|false
 */
function svg($file)
{
    // support for Kirby's file objects
    if (is_a($file, 'Kirby\Cms\File') === true && $file->extension() === 'svg') {
        return $file->read();
    }

    if (is_string($file) === false) {
        return false;
    }

    $extension = F::extension($file);

    // check for valid svg files
    if ($extension !== 'svg') {
        return false;
    }

    // try to convert relative paths to absolute
    if (file_exists($file) === false) {
        $root = App::instance()->root();
        $file = realpath($root . '/' . $file);

        if (file_exists($file) === false) {
            return false;
        }
    }

    return F::read($file);
}

/**
 * Returns translate string for key from translation file
 *
 * @param string|array $key
 * @param string|null $fallback
 * @return mixed
 */
function t($key, string $fallback = null)
{
    return I18n::translate($key, $fallback);
}

/**
 * Translates a count
 *
 * @param string|array $key
 * @param int $count
 * @return mixed
 */
function tc($key, int $count)
{
    return I18n::translateCount($key, $count);
}

/**
 * Translate by key and then replace
 * placeholders in the text
 *
 * @param string $key
 * @param string $fallback
 * @param array $replace
 * @param string $locale
 * @return string
 */
function tt(string $key, $fallback = null, array $replace = null, string $locale = null)
{
    return I18n::template($key, $fallback, $replace, $locale);
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
 * @param array|string|null $options
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
 * @param array|string|null $options
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
    return Html::vimeo($url, $options, $attr);
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
    return Html::youtube($url, $options, $attr);
}
