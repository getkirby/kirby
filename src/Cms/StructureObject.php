<?php

namespace Kirby\Cms;

/**
 * The StructureObject reprents each item
 * in a Structure collection. StructureObjects
 * behave pretty much the same as Pages or Users
 * and have a Content object to access their fields.
 * All fields in a StructureObject are therefor also
 * wrapped in a Field object and can be accessed in
 * the same way as Page fields. They also use the same
 * Field methods.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class StructureObject extends Model
{
    use HasContent;
    use HasSiblings;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var Page|Site|File|User
     */
    protected $parent;

    /**
     * Creates a new StructureObject with the given props
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
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
     * Returns the content
     *
     * @return Content
     */
    public function content(): Content
    {
        if (is_a($this->content, Content::class) === true) {
            return $this->content;
        }

        if (is_array($this->content) !== true) {
            $this->content = [];
        }

        return $this->content = new Content($this->content, $this->parent());
    }

    /**
     * Returns the parent Model object
     *
     * @return Page|Site|File|User
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
    protected function setContent(array $content = null): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Sets the id of the object.
     * The id is required. The structure
     * class will use the index, if no id is
     * specified.
     *
     * @param string $id
     * @return self
     */
    protected function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the parent Model. This can either be a
     * Page, Site, File or User object
     *
     * @param Page|Site|File|User|null $parent
     * @return self
     */
    protected function setParent(Model $parent = null): self
    {
        $this->parent = $parent;
        return $this;
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
