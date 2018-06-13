<?php

use Kirby\Cms\App;
use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Http\Response\Redirect;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\View;

function attr(array $attr = null, $before = null, $after = null)
{
    if ($attrs = Html::attr($attr)) {
        return $before . $attrs . $after;
    }

    return null;
}

/**
 * Creates one or multiple CSS link tags
 *
 * @param string|array $url Relative or absolute URLs, an array of URLs or `@auto` for automatic template css loading
 * @param string|array $attr Pass an array of attributes for the link tag or a media attribute string
 * @return string|null
 */
function css($url, $attr = null)
{
    if (is_array($url) === true) {
        $links = array_map(function ($url) use ($attr) {
            return css($url, $attr);
        }, $url);

        return implode(PHP_EOL, $links);
    }

    $attr = is_array($attr) ? attr($attr, ' ') : attr(['media' => $attr], ' ');
    $tag  = '<link rel="stylesheet" href="%s"' . $attr . '>';

    if ($url === '@auto') {
        if ($assetUrl = Url::toTemplateAsset('css/templates', 'css')) {
            return sprintf($tag, $assetUrl);
        }
    } else {
        return sprintf($tag, Url::to($url));
    }
}

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

function go($url = null, int $code = 301)
{
    die(new Redirect(url($url), $code));
}

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

    if ($uri == '.') {
        $uri = null;
    }

    $page = $uri === '/' ? site() : page($uri);

    if ($page) {
        return $page->image($filename);
    } else {
        return null;
    }
}

function js($src, $async = null)
{
    if (is_array($src) === true) {
        $scripts = array_map(function ($src) use ($async) {
            return js($src, $async);
        }, $src);

        return implode(PHP_EOL, $scripts);
    }

    $tag = '<script src="%s"' . attr(['async' => $async], ' ') . '></script>';

    if ($src === '@auto') {
        if ($assetUrl = Url::toTemplateAsset('js/templates', 'js')) {
            return sprintf($tag, $assetUrl);
        }
    } else {
        return sprintf($tag, Url::to($src));
    }
}

function kirby(): App
{
    return App::instance();
}

function kirbytag($type, string $value = null, array $attr = []): string
{
    if (is_array($type) === true) {
        return App::instance()->kirbytag(key($type), current($type), $type);
    }

    return App::instance()->kirbytag($type, $value, $attr);
}

function kirbytags(string $text = null, array $data = []): string
{
    return App::instance()->kirbytags($text, $data);
}

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

function markdown(string $text = null): string
{
    return App::instance()->markdown($text);
}

function option(string $key, $default = null)
{
    return App::instance()->option($key, $default);
}

function page(...$id)
{
    return App::instance()->site()->find(...$id);
}

function pages(...$id)
{
    return App::instance()->site()->find(...$id);
}

/**
 * Smart version of return with an if condition as first argument
 *
 * @param mixed $condition
 * @param mixed $value The string to be returned if the condition is true
 * @param mixed $alternative An alternative string which should be returned when the condition is false
 * @return null
 */
function r($condition, $value, $alternative = null)
{
    return $condition ? $value : $alternative;
}

function site()
{
    return App::instance()->site();
}

function smartypants(string $text = null): string
{
    return App::instance()->smartypants($text);
}

function snippet($name, $data = [], $return = false)
{
    if (is_object($data) === true) {
        $data = ['item' => $data];
    }

    $snippet = App::instance()->snippet($name, $data);

    if ($snippet->exists() === false) {
        $output = null;
    } else {
        $output = $snippet->render();
    }

    if ($return === true) {
        return $output;
    }

    echo $output;
}

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

function u(string $path = null): string
{
    return Url::to($path);
}

function url(string $path = null): string
{
    return Url::to($path);
}

function video(...$arguments)
{
    return Html::video(...$arguments);
}

function vimeo(...$arguments)
{
    return Html::video(...$arguments);
}

function youtube(...$arguments)
{
    return Html::video(...$arguments);
}
