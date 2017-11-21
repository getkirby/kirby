<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Text\Tags\Tag;

use Html;

/**
 * The email tag can be used to
 * add email links with various attributes
 *
 * Examples
 * ```
 * (email: support@getkirby.com)
 * (email: support@getkirby.com text: Get in contact)
 * ```
 *
 * Check out Kirby\Text\Tag\Link for more available attributes
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Email extends Link
{

    /**
     * Returns all allowed attributes
     * for email links
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
     * Returns the full mailto link
     *
     * I.e. `mailto:support@getkirby.com`
     *
     * @return string
     */
    protected function link(): string
    {
        return 'mailto:' . $this->value();
    }

    /**
     * Returns the text attribute if available
     * and otherwise displays the email address
     *
     * @return string
     */
    protected function text(): string
    {
        return $this->attr('text', $this->value());
    }
}
