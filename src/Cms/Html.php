<?php

namespace Kirby\Cms;

class Html extends \Kirby\Toolkit\Html
{

    public static function a(string $href = null, $text = null, array $attr = []): string
    {
        return parent::a(Url::to($href), $text, $attr);
    }

}
