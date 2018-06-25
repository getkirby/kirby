<?php

namespace Kirby\Cms;

use Exception;

/**
 * Extension of `Kirby\Text\KirbyTags` that introduces
 * `kirbytags:before` and `kirbytags:after` hooks
 */
class KirbyTags extends \Kirby\Text\KirbyTags
{
    public static function parse(string $text = null, array $data = [], array $options = [], array $hooks = []): string
    {
        $text = static::hooks($hooks['kirbytags:before'] ?? [], $text, $data, $options);
        $text = parent::parse($text, $data, $options);
        $text = static::hooks($hooks['kirbytags:after'] ?? [], $text, $data, $options);

        return $text;
    }

    protected static function hooks(array $hooks, string $text = null, array $data, array $options)
    {
        foreach ($hooks as $hook) {
            $text = $hook->call($data['kirby'], $text, $data, $options);
        }

        return $text;
    }
}
