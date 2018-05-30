<?php

namespace Kirby\Cms;

class Html extends \Kirby\Html\Html
{

    public static function a(string $href = null, $text = null, array $attr = []): string
    {
        return parent::a(Url::to($href), $text, $attr);
    }

}
