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
     * @param string|null $text
     * @param array $data
     * @param array $options
     * @param \Kirby\Cms\App|null $app
     * @return string
     */
    public static function parse(string $text = null, array $data = [], array $options = [], ?App $app = null): string
    {
        if ($app !== null) {
            $text = $app->apply('kirbytags:before', compact('text', 'data', 'options'), 'text');
        }

        $text = parent::parse($text, $data, $options);

        if ($app !== null) {
            $text = $app->apply('kirbytags:after', compact('text', 'data', 'options'), 'text');
        }

        return $text;
    }
}
