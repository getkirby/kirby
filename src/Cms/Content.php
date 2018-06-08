<?php

namespace Kirby\Cms;

use Closure;

/**
 * The Content class handles all fields
 * for content from pages, the site and users
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Content
{

    /**
     * The raw data array
     *
     * @var array
     */
    protected $data = [];

    /**
     * Cached field objects
     * Once a field is being fetched
     * it is added to this array for
     * later reuse
     *
     * @var array
     */
    protected $fields = [];

    /**
     * A potential parent object.
     * Not necessarily needed. Especially
     * for testing, but field methods might
     * need it.
     *
     * @var Page|File|User|Site
     */
    protected $parent;

    /**
     * Magic getter for content fields
     *
     * @param string $name
     * @param array $arguments
     * @return Field
     */
    public function __call(string $name, array $arguments = []): Field
    {
        return $this->get($name);
    }

    /**
     * Creates a new Content object
     *
     * @param array $data
     * @param object $parent
     */
    public function __construct($data = [], $parent = null)
    {
        $this->data = $data;
        $this->parent = $parent;
    }

    /**
     * Same as `self::data()` to improve
     * var_dump output
     *
     * @see    self::data()
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the raw data array
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Returns all registered field objects
     *
     * @return array
     */
    public function fields(): array
    {
        foreach ($this->data as $key => $value) {
            $this->get($key);
        }
        return $this->fields;
    }

    /**
     * Returns either a single field object
     * or all registered fields
     *
     * @param   string $key
     * @return  Field|array
     */
    public function get(string $key = null)
    {
        if ($key === null) {
            return $this->fields();
        }

        $key = strtolower($key);

        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        }

        return $this->fields[$key] = new Field($this->parent, $key, $this->data()[$key] ?? null);
    }

    /**
     * Returns all field keys
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->data());
    }

    /**
     * Returns a clone of the content object
     * without the fields, specified by the
     * passed key(s)
     *
     * @param  string ...$keys
     * @return self
     */
    public function not(...$keys): self
    {
        $copy = clone $this;
        $copy->fields = null;

        foreach ($keys as $key) {
            unset($copy->data[$key]);
        }

        return $copy;
    }

    /**
     * Returns the parent
     * Site, Page, File or User object
     *
     * @return Site|Page|File|User
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Set the parent model
     *
     * @param Model $parent
     * @return self
     */
    public function setParent(Model $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Returns the raw data array
     *
     * @see     self::data()
     * @return  array
     */
    public function toArray(): array
    {
        return $this->data();
    }

    /**
     * Updates the content and returns
     * a cloned object
     *
     * @param  array $content
     * @return self
     */
    public function update(array $content = null): self
    {
        $this->data = array_merge($this->data, (array)$content);
        return $this;
    }
}
