<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Schema;
use Kirby\Toolkit\Str;

class Plain extends Schema
{
    public function fallback(string $html)
    {
        $text = trim($html);

        if (Str::length($text) === 0) {
            return false;
        }

        return [
            'type' => 'text',
            'content' => [
                'text' => $text
            ]
        ];
    }

    public function marks(): array
    {
        return [];
    }

    public function nodes(): array
    {
        return [];
    }

    public function skip(): array
    {
        return ['meta', 'script', 'style'];
    }
}
