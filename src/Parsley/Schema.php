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
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
abstract class Schema
{
    /**
     * Returns the fallback block when no
     * other block type can be detected
     *
     * @param string $html
     * @return array|null
     */
    abstract public function fallback(string $html): ?array;

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
    abstract public function skip(): array;
}
