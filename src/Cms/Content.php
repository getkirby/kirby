<?php

namespace Kirby\Cms;

/**
 * The Content class handles all fields
 * for content from pages, the site and users
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
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
     * @var Model
     */
    protected $parent;

    /**
     * Magic getter for content fields
     *
     * @param string $name
     * @param array $arguments
     * @return \Kirby\Cms\Field
     */
    public function __call(string $name, array $arguments = [])
    {
        return $this->get($name);
    }

    /**
     * Creates a new Content object
     *
     * @param array|null $data
     * @param object|null $parent
     */
    public function __construct(array $data = [], $parent = null)
    {
        $this->data   = $data;
        $this->parent = $parent;
    }

    /**
     * Same as `self::data()` to improve
     * `var_dump` output
     *
     * @see self::data()
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /**
     * Converts the content to a new blueprint
     *
     * @param string $to
     * @return array
     */
    public function convertTo(string $to): array
    {
        // prepare data
        $data    = [];
        $content = $this;

        // blueprints
        $old       = $this->parent->blueprint();
        $subfolder = dirname($old->name());
        $new       = Blueprint::factory($subfolder . '/' . $to, $subfolder . '/default', $this->parent);

        // forms
        $oldForm = new Form(['fields' => $old->fields(), 'model' => $this->parent]);
        $newForm = new Form(['fields' => $new->fields(), 'model' => $this->parent]);

        // fields
        $oldFields = $oldForm->fields();
        $newFields = $newForm->fields();

        // go through all fields of new template
        foreach ($newFields as $newField) {
            $name     = $newField->name();
            $oldField = $oldFields->get($name);

            // field name and type matches with old template
            if ($oldField && $oldField->type() === $newField->type()) {
                $data[$name] = $content->get($name)->value();
            } else {
                $data[$name] = $newField->default();
            }
        }

        // preserve existing fields
        return array_merge($this->data, $data);
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
     * @param string|null $key
     * @return \Kirby\Cms\Field|array
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

        // fetch the value no matter the case
        $data  = $this->data();
        $value = $data[$key] ?? array_change_key_case($data)[$key] ?? null;

        return $this->fields[$key] = new Field($this->parent, $key, $value);
    }

    /**
     * Checks if a content field is set
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $key  = strtolower($key);
        $data = array_change_key_case($this->data);

        return isset($data[$key]) === true;
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
     * @param string ...$keys
     * @return self
     */
    public function not(...$keys)
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
     * @return \Kirby\Cms\Model
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Set the parent model
     *
     * @param \Kirby\Cms\Model $parent
     * @return self
     */
    public function setParent(Model $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Returns the raw data array
     *
     * @see self::data()
     * @return array
     */
    public function toArray(): array
    {
        return $this->data();
    }

    /**
     * Updates the content and returns
     * a cloned object
     *
     * @param array|null $content
     * @param bool $overwrite
     * @return self
     */
    public function update(array $content = null, bool $overwrite = false)
    {
        $this->data = $overwrite === true ? (array)$content : array_merge($this->data, (array)$content);

        // clear cache of Field objects
        $this->fields = [];

        return $this;
    }
}
