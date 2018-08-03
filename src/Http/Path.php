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

    public function toString(bool $leadingSlash = false, bool $trailingSlash = false): string
    {
        if (empty($this->data) === true) {
            return '';
        }

        $path = implode('/', $this->data);

        $leadingSlash  = $leadingSlash  === true ? '/' : null;
        $trailingSlash = $trailingSlash === true ? '/' : null;

        return $leadingSlash . $path . $trailingSlash;
    }
}
