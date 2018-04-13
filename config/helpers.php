<?php

use Kirby\Cms\App;
use Kirby\Cms\Url;
use Kirby\Http\Response\Redirect;
use Kirby\Toolkit\View;
use Kirby\Util\F;
use Kirby\Util\I18n;

function css($url)
{
    return '<link rel="stylesheet" href="' . url($url) . '">';
}

function env($key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

function get($key, $default = null)
{
    return App::instance()->request()->query()->get($key, $default);
}

function go($url)
{
    die(new Redirect(url($url)));
}

function js($src)
{
    return '<script src="' . url($src) . '"></script>';
}

function kirby()
{
    return App::instance();
}

function kirbytag($input)
{
    return App::instance()->component('kirbytext')->tag($input);
}

function kirbytext($text)
{
    return App::instance()->component('kirbytext')->parse($text);
}

function markdown($text)
{
    return App::instance()->component('markdown')->parse($text);
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

function smartypants($text)
{
    return App::instance()->component('smartypants')->parse($text);
}

function snippet($name, $data = [], $return = false)
{
    if (is_object($data) === true) {
        $data = ['item' => $data];
    }

    $snippet = App::instance()->component('snippet', $name, $data);

    if ($return === true) {
        return $snippet->render();
    }

    echo $snippet->render();
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

function u(string $path = null): string
{
    return Url::to();
}

function url(string $path = null): string
{
    return Url::to($path);
}

