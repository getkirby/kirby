<?php

namespace Kirby\Cms;

use Exception;

class KirbyText extends \Kirby\Text\KirbyText
{

    public static function parse(string $text = null, array $data = []): string
    {
        $data['kirby']  = $data['kirby']  ?? App::instance();
        $data['site']   = $data['site']   ?? $data['kirby']->site();
        $data['parent'] = $data['parent'] ?? $data['site']->page();

        $text = static::hooks('before', $data['kirby'], $text, $data);
        $text = parent::parse($text, $data);
        $text = static::hooks('after', $data['kirby'], $text, $data);

        return $text;
    }

    protected static function hooks(string $type, App $kirby, string $text = null, array $data): string
    {
        $hooks = $kirby->extensions('hooks')['kirbytext:' . $type] ?? [];

        foreach ($hooks as $hook) {
            $text = $hook->call($kirby, $text, $data);
        }

        return $text;
    }

}
