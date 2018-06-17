<?php

namespace Kirby\Http;

use Kirby\Toolkit\Stack;
use Kirby\Toolkit\Str;

/**
 * A wrapper around an URL path
 * that converts the path into a Kirby stack
 */
class Path extends Stack
{

    protected $trailingSlash = false;

    public function __construct($items)
    {
        if (is_string($items) === true) {
            if (substr($items, -1) === '/') {
                $this->trailingSlash = true;
            }

            $items = Str::split($items, '/');
        }

        parent::__construct($items ?? []);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(bool $leadingSlash = false, bool $trailingSlash = null): string
    {
        $path = implode('/', $this->data);

        $trailingSlash = $trailingSlash !== null ? $trailingSlash : $this->trailingSlash;

        if (empty($path) === false) {
            $path = ($leadingSlash ? '/' : null) . $path . ($trailingSlash ? '/' : null);
        }

        return $path;
    }

    public function trailingSlash(bool $slash)
    {
        $this->trailingSlash = $slash;
        return $this;
    }

}
