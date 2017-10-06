<?php

use Kirby\Cms\App;
use Kirby\Toolkit\View;

function url()
{
    return App::instance()->url();
}

function site()
{
    return App::instance()->site();
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

function css($url)
{
    return '<link rel="stylesheet" href="' . App::instance()->url() . '/' . $url . '">';
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

function kirbytag($input)
{
    return App::instance()->kirbytext()->tag($input);
}

function page(...$id)
{
    return App::instance()->site()->find(...$id);
}

function svg($root)
{
    require App::instance()->root() . '/' . $root;
}
