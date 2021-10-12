<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Schema;
use Kirby\Toolkit\Str;

/**
 * The plain schema definition converts
 * the entire document into simple text blocks
 *
 * @since 3.5.0
 *
 * @package   Kirby Parsley
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Plain extends Schema
{
    /**
     * Creates the fallback block type
     * if no other block can be found
     *
     * @param string $html
     * @return array|null
     */
    public function fallback(string $html): ?array
    {
        $text = trim($html);

        if (Str::length($text) === 0) {
            return null;
        }

        return [
            'type' => 'text',
            'content' => [
                'text' => $text
            ]
        ];
    }

    /**
     * Returns a list of all elements that
     * should be skipped during parsing
     *
     * @return array
     */
    public function skip(): array
    {
        return ['head', 'meta', 'script', 'style'];
    }
}
