<?php

namespace Kirby\Cms;

/**
 * Extension of `Kirby\Text\KirbyTags` that introduces
 * `kirbytags:before` and `kirbytags:after` hooks
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class KirbyTags extends \Kirby\Text\KirbyTags
{
    /**
     * The KirbyTag rendering class
     *
     * @var string
     */
    protected static $tagClass = 'Kirby\Cms\KirbyTag';

    /**
     * @param string $text
     * @param array $data
     * @param array $options
     * @param array $hooks
     * @return string
     */
    public static function parse(string $text = null, array $data = [], array $options = [], array $hooks = []): string
    {
        $text = static::hooks($hooks['kirbytags:before'] ?? [], $text, $data, $options);
        $text = parent::parse($text, $data, $options);
        $text = static::hooks($hooks['kirbytags:after'] ?? [], $text, $data, $options);

        return $text;
    }

    /**
     * Runs the given hooks and returns the
     * modified text
     *
     * @param array $hooks
     * @param string $text
     * @param array $data
     * @param array $options
     * @return string|null
     */
    protected static function hooks(array $hooks, string $text = null, array $data, array $options): ?string
    {
        foreach ($hooks as $hook) {
            $text = $hook->call($data['kirby'], $text, $data, $options);
        }

        return $text;
    }
}
