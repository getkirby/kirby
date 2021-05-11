<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Represents a single block
 * which can be inspected further or
 * converted to HTML
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Block extends Item
{
    const ITEMS_CLASS = '\Kirby\Cms\Blocks';

    /**
     * @var \Kirby\Cms\Content
     */
    protected $content;

    /**
     * @var bool
     */
    protected $isHidden;

    /**
     * @var string
     */
    protected $type;

    /**
     * Proxy for content fields
     *
     * @param string $method
     * @param array $args
     * @return \Kirby\Cms\Field
     */
    public function __call(string $method, array $args = [])
    {
        return $this->content()->get($method);
    }

    /**
     * Creates a new block object
     *
     * @param array $params
     * @param \Kirby\Cms\Blocks $siblings
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        // import old builder format
        $params = BlockConverter::builderBlock($params);
        $params = BlockConverter::editorBlock($params);

        if (isset($params['type']) === false) {
            throw new InvalidArgumentException('The block type is missing');
        }

        $this->content  = $params['content']  ?? [];
        $this->isHidden = $params['isHidden'] ?? false;
        $this->type     = $params['type']     ?? null;

        // create the content object
        $this->content = new Content($this->content, $this->parent);
    }

    /**
     * Converts the object to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Deprecated method to return the block type
     *
     * @deprecated 3.5.0 Use `\Kirby\Cms\Block::type()` instead
     * @todo Add deprecated() helper warning in 3.6.0
     * @todo Remove in 3.7.0
     *
     * @return string
     */
    public function _key(): string
    {
        return $this->type();
    }

    /**
     * Deprecated method to return the block id
     *
     * @deprecated 3.5.0 Use `\Kirby\Cms\Block::id()` instead
     * @todo Add deprecated() helper warning in 3.6.0
     * @todo Remove in 3.7.0
     *
     * @return string
     */
    public function _uid(): string
    {
        return $this->id();
    }

    /**
     * Returns the content object
     *
     * @return \Kirby\Cms\Content
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * Controller for the block snippet
     *
     * @return array
     */
    public function controller(): array
    {
        return [
            'block'   => $this,
            'content' => $this->content(),
            // deprecated block data
            'data'    => $this,
            'id'      => $this->id(),
            'prev'    => $this->prev(),
            'next'    => $this->next()
        ];
    }

    /**
     * Converts the block to HTML and then
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
     * Checks if the block is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->content()->toArray());
    }

    /**
     * Checks if the block is hidden
     * from being rendered in the frontend
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * Checks if the block is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->isEmpty() === false;
    }

    /**
     * Returns the block type
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * The result is being sent to the editor
     * via the API in the panel
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'content'  => $this->content()->toArray(),
            'id'       => $this->id(),
            'isHidden' => $this->isHidden(),
            'type'     => $this->type(),
        ];
    }

    /**
     * Converts the block to html first
     * and then places that inside a field
     * object. This can be used further
     * with all available field methods
     *
     * @return \Kirby\Cms\Field
     */
    public function toField()
    {
        return new Field($this->parent(), $this->id(), $this->toHtml());
    }

    /**
     * Converts the block to HTML
     *
     * @return string
     */
    public function toHtml(): string
    {
        try {
            return (string)snippet('blocks/' . $this->type(), $this->controller(), true);
        } catch (Throwable $e) {
            return '<p>Block error: "' . $e->getMessage() . '" in block type: "' . $this->type() . '"</p>';
        }
    }
}
