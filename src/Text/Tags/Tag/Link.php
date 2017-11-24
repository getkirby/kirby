<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Text\Tags\Tag;
use Kirby\Html\Element;
use Kirby\Html\Element\A;

/**
 * Creates a link in your text
 *
 * The syntax for this tag is intended
 * to be as straight forward as possible.
 * Especially to replace the weird Markdown
 * syntax for links, which I can never
 * remember when I need it.
 *
 * Examples:
 * ```
 * (link: https://getkirby.com)
 * (link: https://getkirby.com text: Kirby)
 * (link: https://getkirby.com text: Kirby class: link)
 * (link: https://getkirby.com text: Kirby rel: me)
 * (link: https://getkirby.com text: Kirby target: _blank)
 * ```
 *
 * Check out the attributes method for more allowed attributes
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Link extends Tag
{

    /**
     * Returns all allowed attributes
     * for the link tag
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'text',
            'class',
            'role',
            'title',
            'rel',
            'target',
            'popup'
        ];
    }

    /**
     * Creates an Kirby\HTML\Element\A for the link
     * and adds all attributes.
     *
     * @return A
     */
    public function element(): Element
    {
        return new A($this->link(), $this->text(), [
            'rel'    => $this->attr('rel'),
            'class'  => $this->attr('class'),
            'role'   => $this->attr('role'),
            'title'  => $this->attr('title'),
            'target' => $this->target(),
        ]);
    }

    /**
     * Converts the A element to string
     *
     * @return  string
     */
    protected function html(): string
    {
        return $this->element()->toString();
    }

    /**
     * Returns the href value
     * This method is especially used in
     * subclasses to provide different link
     * variations more easily.
     *
     * @return string
     */
    protected function link(): string
    {
        return $this->value();
    }

    /**
     * If the text attribute is set,
     * that will be used for the link text.
     * Otherwise the URL will be used.
     *
     * @return string
     */
    protected function text(): string
    {
        return $this->attr('text', $this->link());
    }

    /**
     * The target attribute can have a regular
     * value, such as _blank, but there's also
     * a custom popup value, which can be set to
     * yes or true or something similar to switch on
     * target _blank in a less techie way.
     *
     * @return string
     */
    protected function target(): string
    {
        if (empty($this->attr('target')) === false) {
            return $this->attr('target');
        } elseif (empty($this->attr('popup')) === false) {
            return '_blank';
        } else {
            return '';
        }
    }
}
