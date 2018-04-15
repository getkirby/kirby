<?php

namespace Kirby\Cms\Tags;

use Kirby\Html\Element;
use Kirby\Cms\Url;

class Image extends \Kirby\Text\Tags\Image
{
    use Dependencies;

    /**
     * Returns the list of allowed attributes for the image
     *
     * @return array
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'caption',
            'class'
        ]);
    }

    /**
     * Creates a figcaption element
     * if the caption attribute is set
     *
     * @return Element|null
     */
    protected function caption()
    {
        if ($caption = $this->attr('caption')) {
            return new Element('figcaption', $caption);
        }
    }

    /**
     * Creates the figure wrapper for the image
     *
     * @return Element
     */
    protected function figure(): Element
    {
        return new Element('figure', [
            'class' => $this->attr('class'),
        ]);
    }

    /**
     * Renders the image tag
     *
     * @return string
     */
    protected function html(): string
    {
        $figure = $this->figure()->html([
            parent::html(),
            $this->caption()
        ]);

        return $figure;
    }

    /**
     * Resolves the link url
     *
     * @return string
     */
    protected function linkUrl(): string
    {
        $link = $this->attr('link');

        if ($link === 'self') {
            return $this->src();
        }

        return Url::to($link);
    }

    /**
     * Resolves the image url
     *
     * @return string
     */
    protected function src(): string
    {
        $src = $this->value();

        if ($file = $this->file($src)) {
            return $file->url();
        }

        return Url::to($src);
    }
}
