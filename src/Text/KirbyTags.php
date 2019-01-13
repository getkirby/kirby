<?php

namespace Kirby\Text;

use Exception;

/**
 * Parses and converts custom kirbytags in any
 * given string. KiryTags are defined via
 * `KirbyTag::$types`. The default tags for the
 * Cms are located in `kirby/config/tags.php`
 */
class KirbyTags
{
    protected static $tagClass = KirbyTag::class;

    public static function parse(string $text = null, array $data = [], array $options = []): string
    {
        return preg_replace_callback('!(?=[^\]])\([a-z0-9_-]+:.*?\)!is', function ($match) use ($data, $options) {
            try {
                return static::$tagClass::parse($match[0], $data, $options)->render();
            } catch (Exception $e) {
                return $match[0];
            }
        }, $text);
    }
}
