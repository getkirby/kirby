<?php

namespace Kirby\Cms;

class Config
{

    public static function for(App $app): array
    {
        // TODO: implement host detection
        $host = 'localhost';
        $root = $app->roots()->config();

        $main = static::load($root . '/config.php');
        $host = static::load($root . '/config.' . $host . '.php');

        return array_merge($main, $host);
    }

    public static function load(string $file): array
    {
        return file_exists($file) === true ? (array)include $file : [];
    }

}
