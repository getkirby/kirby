<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Str;

/**
 * The System class gathers all information
 * about the server, PHP and other environment
 * parameters and checks for a valid setup.
 *
 * This is mostly used by the panel installer
 * to check if the panel can be installed at all.
 */
class System
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;

        // try to create all folders that could be missing
        $this->init();
    }

    public function status(): array
    {
        return [
            'accounts'  => $this->accounts(),
            'content'   => $this->content(),
            'curl'      => $this->curl(),
            'mbstring'  => $this->mbstring(),
            'media'     => $this->media(),
            'php'       => $this->php(),
            'server'    => $this->server(),
        ];
    }

    public function accounts(): bool
    {
        return is_writable($this->app->root('accounts'));
    }

    public function content(): bool
    {
        return is_writable($this->app->root('content'));
    }

    public function curl(): bool
    {
        return extension_loaded('curl');
    }

    public function init()
    {
        Dir::make($this->app->root('accounts'));
        Dir::make($this->app->root('content'));
        Dir::make($this->app->root('media'));
    }

    public function isInstalled(): bool
    {
        return $this->app->users()->count() > 0;
    }

    public function isLocal(): bool
    {
        $server = $this->app->server();
        $host   = $server->host();

        if ($host === 'localhost') {
            return true;
        }

        if (in_array($server->address(), ['::1', '127.0.0.1', '0.0.0.0']) === true) {
            return true;
        }

        if (Str::endsWith($host, '.dev') === true) {
            return true;
        }

        if (Str::endsWith($host, '.local') === true) {
            return true;
        }

        if (Str::endsWith($host, '.test') === true) {
            return true;
        }

        return false;
    }

    public function isOk(): bool
    {
        return in_array(false, array_values($this->status()), true) === false;
    }

    /**
     * Loads the license file and returns
     * the license information if available
     *
     * @return array|false
     */
    public function license()
    {
        $file = $this->app->root('config') . '/license.php';

        if (file_exists($file) === false) {
            return false;
        }

        $license = (array)require $file;

        if (isset($license['code'], $license['type'], $license['issued']) === false) {
            return false;
        }

        return $license;
    }

    public function mbString(): bool
    {
        return extension_loaded('mbstring');
    }

    public function media(): bool
    {
        return is_writable($this->app->root('media'));
    }

    public function php(): bool
    {
        return version_compare(phpversion(), '7.0.0', '>');
    }

    public function server(): bool
    {
        $servers = [
            'apache',
            'caddy',
            'litespeed',
            'nginx',
            'php'
        ];

        $software = $_SERVER['SERVER_SOFTWARE'] ?? null;

        return preg_match('!(' . implode('|', $servers) . ')!i', $software) > 0;
    }

    /**
     * Upgrade to the new folder separator
     *
     * @param string $root
     * @return void
     */
    public static function upgradeContent(string $root)
    {
        $index = Dir::read($root);

        foreach ($index as $dir) {
            $oldRoot = $root . '/' . $dir;
            $newRoot = preg_replace('!\/([0-9]+)\-!', '/$1_', $oldRoot);

            if (is_dir($oldRoot) === true) {
                Dir::move($oldRoot, $newRoot);
                static::upgradeContent($newRoot);
            }
        }
    }

    public function toArray(): array
    {
        return $this->status();
    }
}
