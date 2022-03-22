<?php

namespace Kirby\Parsley;

/**
 * Block schema definition
 *
 * @since 3.5.0
 *
 * @package   Kirby Parsley
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Schema
{
    /**
     * Returns the fallback block when no
     * other block type can be detected
     *
     * @param \Kirby\Parsley\Element|string $element
     * @return array|null
     */
    public function fallback($element): ?array
    {
        return null;
    }

    /**
     * Returns a list of allowed inline marks
     * and their parsing rules
     *
     * @return array
     */
    public function marks(): array
    {
        return [];
    }

    /**
     * Returns a list of allowed nodes and
     * their parsing rules
     *
     * @return array
     */
    public function nodes(): array
    {
        return [];
    }

    /**
     * Returns a list of all elements that should be
     * skipped and not be parsed at all
     *
     * @return array
     */
    public function skip(): array
    {
        return [];
    }
}
