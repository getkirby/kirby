<?php

namespace Kirby\Cms;

use Kirby\Data\Json;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * A collection of blocks
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Blocks extends Items
{
    const ITEM_CLASS = '\Kirby\Cms\Block';

    /**
     * Return HTML when the collection is
     * converted to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Converts the blocks to HTML and then
     * uses the Str::excerpt method to create
     * a non-formatted, shortened excerpt from it
     *
     * @param mixed ...$args
     * @return string
     */
    public function excerpt(...$args)
    {
        return Str::excerpt($this->toHtml(), ...$args);
    }

    /**
     * Parse and sanitize various block formats
     *
     * @param array|string $input
     * @return array
     */
    public static function parse($input): array
    {
        if (is_array($input) === false) {
            try {
                $input = Json::decode((string)$input);
            } catch (Throwable $e) {
                // try to import the old YAML format
                $input = Yaml::decode((string)$input);
            }
        }

        if (empty($input) === true) {
            return [];
        }

        return $input;
    }

    /**
     * Convert all blocks to HTML
     *
     * @return string
     */
    public function toHtml(): string
    {
        $html = [];

        foreach ($this->data as $block) {
            $html[] = $block->toHtml();
        }

        return implode($html);
    }
}
