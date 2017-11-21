<?php

namespace Kirby\Content;

use Exception;
use Kirby\Data\Data;
use Kirby\FileSystem\File;

/**
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Content
{

    /**
     * The file object
     *
     * @var File
     */
    protected $file;

    /**
     * The parsed data array from the content file
     *
     * @var array|null
     */
    protected $data = null;

    /**
     * An array of Field objects
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Creates a new content object
     *
     * @param  File|string $file
     */
    public function __construct($file)
    {
        if (is_a($file, File::class)) {
            $this->file = $file;
        } elseif (is_string($file) === true) {
            $this->file = new File($file);
        } else {
            throw new Exception('Invalid content file type: ' . gettype($file));
        }
    }

    /**
     * Returns the content file object
     *
     * @return File
     */
    public function file(): File
    {
        return $this->file;
    }

    /**
     * Checks if the content file actually exists
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return $this->file->exists();
    }

    /**
     * Returns the parsed data array from the content file
     *
     * @return array
     */
    public function data(): array
    {
        if ($this->data === null) {
            try {
                return $this->data = Data::read($this->file->realpath());
            } catch (Exception $e) {
                return $this->data = [];
            }
        }

        return $this->data;
    }

    /**
     * Returns all field objects as array
     *
     * @return array
     */
    public function fields(): array
    {
        $result = [];

        foreach ($this->data() as $key => $value) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    /**
     * Creates a new Field object, which
     * can be added to the $fields array afterwards.
     *
     * @param  string  $key
     * @return Field
     */
    public function field(string $key): Field
    {
        return new Field($key, $this->data()[$key] ?? '');
    }

    /**
     * Get a Field object by key/field name.
     *
     * @param  string $key
     * @return Field
     */
    public function get(string $key): Field
    {
        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        } else {
            return $this->fields[$key] = $this->field($key);
        }
    }

    /**
     * Stores the given data in the content array
     *
     * @param  array   $data
     * @return Content
     */
    public function save(array $data = []): self
    {
        Data::write($this->file->root(), $data);

        // flush stored data
        $this->data   = null;
        $this->fields = [];

        return $this;
    }

    /**
     * Renames the content file
     *
     * @param  string $newName
     * @return Content
     */
    public function rename(string $newName): self
    {
        $this->file->rename($newName);
        return $this;
    }

    /**
     * Converts the content object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data();
    }

    /**
     * Magic getter for Field objects
     *
     * @param  string $key
     * @param  null   $args Args won't have any effect.
     * @return Field
     */
    public function __call(string $key, $args = null): Field
    {
        return $this->get($key);
    }
}
