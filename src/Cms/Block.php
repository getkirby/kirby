<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Throwable;

/**
 * Represents a single block
 * from the builder, structure field or editor,
 * which can be inspected further or
 * converted to HTML
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Block
{
    use HasSiblings;

    /**
     * @var \Kirby\Cms\Content
     */
    protected $attrs;

    /**
     * @var \Kirby\Cms\Content
     */
    protected $content;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var \Kirby\Cms\Page|\Kirby\Cms\Site|\Kirby\Cms\User|\Kirby\Cms\File
     */
    protected $parent;

    /**
     * @var \Kirby\Cms\Blocks
     */
    protected $siblings;

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
        if ($this->content()->has($method)) {
            return $this->content()->get($method);
        }

        return $this->attrs()->get($method);
    }

    /**
     * Creates a new block object
     *
     * @param array $params
     * @param \Kirby\Cms\Blocks $siblings
     */
    public function __construct(array $params)
    {
        // import old block format
        if (isset($params['_key']) === true) {
            $params['type']    = $params['_key'];
            $params['content'] = $params;
            unset($params['_uid']);
        }

        if (isset($params['type']) === false) {
            throw new InvalidArgumentException('The block type is missing');
        }

        $this->attrs    = $params['attrs']    ?? [];
        $this->content  = $params['content']  ?? [];
        $this->field    = $params['field']    ?? 'blocks';
        $this->id       = $params['id']       ?? uuid();
        $this->parent   = $params['parent']   ?? site();
        $this->siblings = $params['siblings'] ?? new Blocks();
        $this->type     = $params['type']     ?? null;

        // create the content & attrs object
        $this->attrs   = new Content($this->attrs, $this->parent);
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
     * @return string
     */
    public function _key(): string
    {
        return $this->type();
    }

    /**
     * Deprecated method to return the block id
     *
     * @return string
     */
    public function _uid(): string
    {
        return $this->id();
    }

    /**
     * Returns a specific attribute
     *
     * @param string $name
     * @param mixed $default
     * @return \Kirby\Cms\Field
     */
    public function attr(string $name, $default = null)
    {
        return $this->attrs()->get($name)->or($default);
    }

    /**
     * Returns the attrs object
     *
     * @return \Kirby\Cms\Content
     */
    public function attrs()
    {
        return $this->attrs;
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
            'attrs'   => $this->attrs(),
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
     * Block factory
     *
     * @param array $params
     * @return \Kirby\Cms\Block
     */
    public static function factory(array $params)
    {
        return new static($params);
    }

    /**
     * Returns the type of field this
     * block belongs to (structure | builder | editor)
     *
     * @return string
     */
    public function field(): string
    {
        return $this->field;
    }

    /**
     * Returns the block id (UUID v4)
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Compares the block to another one
     *
     * @param \Kirby\Cms\Block $block
     * @return bool
     */
    public function is(Block $block): bool
    {
        return $this->id() === $block->id();
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
     * Checks the hide attribute
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->attr('hide')->isTrue();
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
     * Returns the Kirby instance
     *
     * @return \Kirby\Cms\App
     */
    public function kirby()
    {
        return $this->parent()->kirby();
    }

    /**
     * Returns the parent model
     *
     * @return \Kirby\Cms\Page | \Kirby\Cms\Site | \Kirby\Cms\File | \Kirby\Cms\User
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Returns the sibling collection
     * This is required by the HasSiblings trait
     *
     * @return \Kirby\Editor\Blocks
     */
    protected function siblingsCollection()
    {
        return $this->siblings;
    }

    /**
     * Returns the path to the rendered snippet
     *
     * @return string
     */
    public function snippet(): string
    {
        return implode('/', [
            $this->field(),
            $this->type()
        ]);
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
            'attrs'   => $this->attrs()->toArray(),
            'content' => $this->content()->toArray(),
            'id'      => $this->id(),
            'type'    => $this->type(),
        ];
    }

    /**
     * Converts the block to html first
     * and then places that inside a field
     * object. This can be used further
     * with all available field methods
     *
     * @return \Kirby\Cms\Field;
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
            return (string)snippet([$this->snippet(), 'blocks/' . $this->type()], $this->controller(), true);
        } catch (Throwable $e) {
            return '<p>Block error: "' . $e->getMessage() . '" in block type: "' . $this->type() . '"</p>';
        }
    }
}
