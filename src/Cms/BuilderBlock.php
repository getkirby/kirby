<?php

namespace Kirby\Cms;

/**
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class BuilderBlock extends Model
{
    use HasSiblings;

    /**
     * The content
     *
     * @var Content
     */
    protected $content;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var Page|Site|File|User
     */
    protected $parent;

    /**
     * The parent BuilderBlocks collection
     *
     * @var BuilderBlocks
     */
    protected $blocks;

    /**
     * Modified getter to also return fields
     * from the object's content
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        // public property access
        if (isset($this->$method) === true) {
            return $this->$method;
        }

        return $this->content()->get($method, $arguments);
    }

    /**
     * Creates a new BuilderBlock with the given props
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    /**
     * Creates the HTML for the block with the
     * matching snippet
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)snippet('blocks/' . $this->_key(), ['data' => $this]);
    }

    /**
     * Returns the content
     *
     * @return \Kirby\Cms\Content
     */
    public function content()
    {
        if (is_a($this->content, 'Kirby\Cms\Content') === true) {
            return $this->content;
        }

        if (is_array($this->content) !== true) {
            $this->content = [];
        }

        return $this->content = new Content($this->content, $this->parent());
    }

    /**
     * Returns the required id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Compares the current object with the given BuilderBlock object
     *
     * @param mixed $builderBlock
     * @return bool
     */
    public function is($builderBlock): bool
    {
        if (is_a($builderBlock, 'Kirby\Cms\BuilderBlock') === false) {
            return false;
        }

        return $this === $builderBlock;
    }

    /**
     * Returns the parent Model object
     *
     * @return \Kirby\Cms\ModelWithContent
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Sets the Content object with the given parent
     *
     * @param array|null $content
     * @return self
     */
    protected function setContent(array $content = null)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Sets the id of the object.
     * The id is required. The BuilderBlocks
     * class will use the index, if no id is
     * specified.
     *
     * @param string $id
     * @return self
     */
    protected function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the parent ModelWithContent. This can either be a
     * Page, Site, File or User object
     *
     * @param \Kirby\Cms\ModelWithContent|null $parent
     * @return self
     */
    protected function setParent(ModelWithContent $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Sets the parent BuilderBlocks collection
     *
     * @param \Kirby\Cms\BuilderBlocks|null $blocks
     * @return self
     */
    protected function setBlocks(BuilderBlocks $blocks = null)
    {
        $this->blocks = $blocks;
        return $this;
    }

    /**
     * Returns the parent Structure collection as siblings
     *
     * @return \Kirby\Cms\Structure
     */
    protected function siblingsCollection()
    {
        return $this->structure;
    }

    /**
     * Converts all fields in the object to a
     * plain associative array. The id is
     * injected into the array afterwards
     * to make sure it's always present and
     * not overloaded in the content.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = $this->content()->toArray();
        $array['id'] = $this->id();

        ksort($array);

        return $array;
    }
}
