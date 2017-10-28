<?php

use Kirby\Cms\App;
use Kirby\Http\Response\Redirect;
use Kirby\Toolkit\View;


function css($url)
{
    return '<link rel="stylesheet" href="' . url($url) . '">';
}

function go($url) {
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
    return kirby()->kirbytext()->tag($input);
}

function kirbytext($text)
{
    return kirby()->kirbytext()->parse($text);
}

function markdown($text)
{
    return kirby()->markdown()->parse($text);
}

function page(...$id)
{
    return site()->find(...$id);
}

function pages(...$id)
{
    return site()->find(...$id);
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
    return kirby()->site();
}

function smartypants($text)
{
    return kirby()->smartypants()->parse($text);
}

/**
 * Helpers
 */
function snippet($name, $data = [], $return = false)
{
    if (is_object($data)) {
        $data = ['item' => $data];
    }

    $snippet = new View(App::instance()->root('snippets') . '/' . $name . '.php', $data);
    $snippet->toString();

    if ($return) {
        return $snippet;
    }

    echo $snippet;
}

function svg($root)
{
    require kirby()->root() . '/' . $root;
}

function url($path = null)
{
    return rtrim(kirby()->url() . '/' . $path, '/');
}

