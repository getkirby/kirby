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
     * JSON string
     *
     * @param string|array $value
     * @param array $params
     * @return \Kirby\Cms\Blocks
     */
    public static function factory($blocks, array $params = [])
    {
        $options = array_merge([
            'options' => [],
            'parent'  => App::instance()->site(),
            'type'    => null
        ], $params);

        if (empty($blocks) === true) {
            return new static();
        }

        if (is_array($blocks) === false) {
            try {
                $blocks = Json::decode((string)$blocks);
            } catch (Throwable $e) {
                // try to import the old YAML format
                $blocks = Yaml::decode((string)$blocks);
            }
        }

        if (!is_array($blocks) === true) {
            return new static();
        }

        // import deprecated blocks
        $blocks = static::import($blocks);

        // pass the type to the options array if not given
        $options['type'] = $options['type'] ?? $blocks['type'];

        // create a new collection of blocks
        $collection = new static([], $options);

        foreach ($blocks['blocks'] as $params) {
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
     * Import deprecated block formats
     *
     * @param array $blocks
     * @return array
     */
    public static function import(array $input = []): array
    {
        if (empty($input) === true) {
            return [
                'type'   => null,
                'blocks' => []
            ];
        }

        // the format is already up-to-date
        if (isset($input['blocks']) === true) {
            return $input;
        }

        $type   = null;
        $blocks = $input;

        // check for builder blocks
        if (isset($blocks[0]['_key']) === true) {
            $type = 'builder';
            $blocks = array_map(function ($block) {
                $block['content'] = $block;
                $block['id']      = uuid();
                $block['type']    = $block['_key'];
                return $block;
            }, $blocks);

        // import blocks as structure
        } else {
            $type   = 'structure';
            $blocks = array_map(function ($block) {
                $block['content'] = $block;
                $block['id']      = uuid();
                $block['type']    = 'default';
                return $block;
            }, $blocks);
        }

        return [
            'type'   => $type,
            'blocks' => $blocks
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
