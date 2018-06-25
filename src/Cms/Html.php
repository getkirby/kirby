<?php

namespace Kirby\Cms;

/**
 * Custom extension of the Toolkit Html builder class
 * that overwrites the Html::a method to include Cms
 * Url handling.
 */
class Html extends \Kirby\Toolkit\Html
{
    public static function a(string $href = null, $text = null, array $attr = []): string
    {
        return parent::a(Url::to($href), $text, $attr);
    }
}
