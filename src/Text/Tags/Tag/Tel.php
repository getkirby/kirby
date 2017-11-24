<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Text\Tags\Tag;

/**
 * The Tel tag creates a phone number
 * link in your text, which can be used on
 * mobile phones for example to start a call.
 *
 * Examples:
 * ```
 * (tel: +49 1234 5678)
 * (tel: +49 1234 5678 text: Call Us!)
 * ```
 *
 * Check out the attributes method for
 * additional available attributes.
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Tel extends Link
{

    /**
     * Returns all allowed attributes
     * for telephone links
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
            'rel'
        ];
    }

    /**
     * Converts the value to a machine-readable phone number
     *
     * @return string
     */
    protected function number(): string
    {
        return preg_replace('![^0-9\+]+!', '', $this->value());
    }

    /**
     * Creates the tel link
     *
     * @return string
     */
    protected function link(): string
    {
        return 'tel:' . $this->number();
    }

    /**
     * Returns the text or the phone number as fall back
     *
     * @return string
     */
    protected function text(): string
    {
        return $this->attr('text', $this->value());
    }
}
