<?php

namespace Kirby\Text;

use Exception;

class KirbyText
{

    public static function parse(string $text = null, array $data = []): string
    {
        // apply data to all tags
        KirbyTag::$data = $data;

        return preg_replace_callback('!(?=[^\]])\([a-z0-9_-]+:.*?\)!is', function ($match) {
            try {
                return KirbyTag::parse($match[0]);
            } catch (Exception $e) {
                return $match[0];
            }
        }, $text);
    }

}
