<?php

namespace Kirby\Cms;

/**
 * Custom extension of the Toolkit Html builder class
 * that overwrites the Html::a method to include Cms
 * Url handling.
 */
class Html extends \Kirby\Toolkit\Html
{
    /**
     * Generates an a tag with an absolute Url
     *
     * @param string $href Relative or absolute Url
     * @param string|array|null $text If null, the link will be used as link text. If an array is passed, each element will be added unencoded
     * @param array $attr Additional attributes for the a tag.
     * @return string
     */
    public static function a(string $href = null, $text = null, array $attr = []): string
    {
        return parent::a(Url::to($href), $text, $attr);
    }
}
