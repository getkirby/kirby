<?php

namespace Kirby\Cms;

use Closure;
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
class Blocks extends Collection
{

    /**
     * Constructor
     *
     * @param array $objects
     * @param array $options
     */
    public function __construct($objects = [], array $options = [])
    {
        $this->parent = $options['parent'] ?? App::instance()->site();
        parent::__construct($objects, $this->parent);
    }

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
     * Creates a new block collection from a
     * an array of block props
     *
     * @param array $blocks
     * @param array $params
     * @return \Kirby\Cms\Blocks
     */
    public static function factory(array $blocks = null, array $params = [])
    {
        $options = array_merge([
            'options' => [],
            'parent'  => App::instance()->site(),
        ], $params);

        if (empty($blocks) === true || is_array($blocks) === false) {
            return new static();
        }

        // create a new collection of blocks
        $collection = new static([], $options);

        foreach ($blocks as $params) {
            $params['options']  = $options['options'];
            $params['parent']   = $options['parent'];
            $params['siblings'] = $collection;
            $block = Block::factory($params);
            $collection->append($block->id(), $block);
        }

        return $collection;
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
     * Convert the blocks to an array
     *
     * @return array
     */
    public function toArray(Closure $map = null): array
    {
        return array_values(parent::toArray($map));
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
