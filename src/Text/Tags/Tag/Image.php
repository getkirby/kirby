<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Html\Element;
use Kirby\Html\Element\Img;
use Kirby\Html\Element\A;
use Kirby\Text\Tags\Tag;

/**
 * Embeds an image in your text
 *
 * Examples:
 * ```
 * (image: myimage.jpg)
 * (image: myimage.jpg alt: My image)
 * (image: myimage.jpg alt: My image width: 300 height: 200)
 * (image: myimage.jpg alt: My image link: https://getkirby.com)
 * ```
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Image extends Tag
{

    /**
     * Returns the list of allowed attributes for the image
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'link',
            'alt',
            'width',
            'height'
        ];
    }

    /**
     * Returns the Kirby\HTML\Element\Img
     * with the given attributes
     *
     * @return Img
     */
    public function element(): Element
    {
        return new Img($this->value(), [
            'alt'    => $this->attr('alt'),
            'width'  => $this->attr('width'),
            'height' => $this->attr('height'),
        ]);
    }

    /**
     * Wraps the image in an A element
     * if the link attribute is set
     *
     * @return false|A
     */
    protected function link()
    {
        if (empty($this->attr('link')) !== true) {
            return new A($this->attr('link'));
        } else {
            return false;
        }
    }

    /**
     * Renders the image tag
     *
     * @return string
     */
    protected function html(): string
    {
        $image = $this->element();
        $link  = $this->link();

        if ($link !== false) {
            $image = $image->wrap($link);
        }

        return $image;
    }
}
