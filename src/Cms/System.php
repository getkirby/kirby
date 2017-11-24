<?php

namespace Kirby\Cms;

use Kirby\Util\Str;

class System
{

    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function status(): array
    {
        return [
            'php'       => $this->php(),
            'server'    => $this->server(),
            'mbstring'  => $this->mbstring(),
            'curl'      => $this->curl(),
            'media'     => $this->media(),
            'accounts'  => $this->accounts(),
            'content'   => $this->content(),
        ];
    }

    public function isOk(): bool
    {
        return in_array(false, array_values($this->status()), true) === false;
    }

    public function isInstalled(): bool
    {
        return $this->app->users()->count() > 0;
    }

    public function php(): bool
    {
        return version_compare(phpversion(), '7.0.0', '>');
    }

    public function server(): bool
    {
        $software = strtolower($_SERVER['SERVER_SOFTWARE'] ?? null);
        return (Str::contains($software, 'apache') || Str::contains($software, 'nginx'));
    }

    public function mbString(): bool
    {
        return extension_loaded('mbstring');
    }

    public function curl(): bool
    {
        return extension_loaded('curl');
    }

    public function media(): bool
    {
        return is_writable($this->app->root('media'));
    }

    public function accounts(): bool
    {
        return is_writable($this->app->root('accounts'));
    }

    public function content(): bool
    {
        return is_writable($this->app->root('content'));
    }

    public function toArray(): array
    {
        return $this->status();
    }

}
