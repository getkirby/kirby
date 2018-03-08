<?php

namespace Kirby\Text\Tags;

use Kirby\Html\Element;
use Kirby\Html\Element\Img;
use Kirby\Html\Element\A;

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
            'alt',
            'height',
            'imgClass',
            'link',
            'linkClass',
            'rel',
            'target',
            'title',
            'width',
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
        return new Img($this->src(), [
            'alt'    => $this->attr('alt'),
            'class'  => $this->attr('imgClass'),
            'height' => $this->attr('height'),
            'title'  => $this->attr('title'),
            'width'  => $this->attr('width'),
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
            return new A($this->linkUrl(), '', [
                'class'  => $this->attr('linkClass'),
                'rel'    => $this->attr('rel'),
                'target' => $this->attr('target')
            ]);
        } else {
            return false;
        }
    }

    protected function linkUrl(): string
    {
        return $this->attr('link');
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

    protected function src(): string
    {
        return $this->value();
    }

}
