<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Json;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * A collection of blocks
 * from the builder, structure field or editor
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
     * structure | builder | editor
     * @var string
     */
    protected $type = null;

    /**
     * Constructor
     *
     * @param array $objects
     * @param array $options
     */
    public function __construct($objects = [], array $options = [])
    {
        $this->parent = $options['parent'] ?? App::instance()->site();
        $this->type   = $options['type']   ?? null;
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
            'type'    => null
        ], $params);

        if (empty($blocks) === true || is_array($blocks) === false) {
            return new static();
        }

        // create a new collection of blocks
        $collection = new static([], $options);

        foreach ($blocks as $params) {
            $params['field']    = $options['type'];
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
     * @param string $type Expected field type
     * @return array
     */
    public static function parse($input, string $type = null): array
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
            return [
                'type'   => $type,
                'blocks' => []
            ];
        }

        // the format is already up-to-date
        if (array_key_exists('blocks', $input) === true) {
            $input['type'] = $input['type'] ?? $type;
            return $input;
        }

        // check for builder blocks
        if (array_key_exists('_key', $input[0]) === true) {
            $type = $type ?? 'builder';
        // import blocks as structure
        } else {
            $type = $type ?? 'structure';
        }


        return [
            'type'   => $type,
            'blocks' => $input
        ];
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

    /**
     * Returns the block type
     *
     * @return string|null
     */
    public function type(): ?string
    {
        return $this->type;
    }
}
