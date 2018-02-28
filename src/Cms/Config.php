<?php

namespace Kirby\Cms;

use Dotenv\Dotenv;

class Config
{

    public static function for(App $app): array
    {
        // load env settings
        static::env($app->roots()->env());

        // TODO: implement host detection
        $host = 'localhost';
        $root = $app->roots()->config();

        $main    = static::load($root . '/config.php');
        $host    = static::load($root . '/config.' . $host . '.php');
        $plugins = $app->get('option');

        return array_merge($plugins, $main, $host);
    }

    public static function load(string $file): array
    {
        return file_exists($file) === true ? (array)include $file : [];
    }

    public static function env(string $root): bool
    {
        if (file_exists($root . '/.env') !== true) {
            return false;
        }

        $dotenv = new Dotenv($root);
        $dotenv->load();

        return true;
    }

}
