<?php

namespace Kirby\Toolkit;

use Exception;

/**
 * Flexible File object with a set of helpful
 * methods to inspect and work with files.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class File
{
    /**
     * Absolute file path
     *
     * @var string
     */
    protected $root;

    /**
     * Constructs a new File object by absolute path
     *
     * @param string $root Absolute file path
     */
    public function __construct(string $root = null)
    {
        $this->root = $root;
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the file content as base64 encoded string
     *
     * @return string
     */
    public function base64(): string
    {
        return base64_encode($this->read());
    }

    /**
     * Copy a file to a new location.
     *
     * @param string $target
     * @param bool $force
     * @return self
     */
    public function copy(string $target, bool $force = false)
    {
        if (F::copy($this->root, $target, $force) !== true) {
            throw new Exception('The file "' . $this->root . '" could not be copied');
        }

        return new static($target);
    }

    /**
     * Returns the file as data uri
     *
     * @param bool $base64 Whether the data should be base64 encoded or not
     * @return string
     */
    public function dataUri(bool $base64 = true): string
    {
        if ($base64 === true) {
            return 'data:' . $this->mime() . ';base64,' . $this->base64();
        }

        return 'data:' . $this->mime() . ',' . Escape::url($this->read());
    }

    /**
     * Deletes the file
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (F::remove($this->root) !== true) {
            throw new Exception('The file "' . $this->root . '" could not be deleted');
        }

        return true;
    }

    /**
     * Checks if the file actually exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->root) === true;
    }

    /**
     * Returns the current lowercase extension (without .)
     *
     * @return string
     */
    public function extension(): string
    {
        return F::extension($this->root);
    }

    /**
     * Returns the filename
     *
     * @return string
     */
    public function filename(): string
    {
        return basename($this->root);
    }

    /**
     * Returns a md5 hash of the root
     *
     * @return string
     */
    public function hash(): string
    {
        return md5($this->root);
    }

    /**
     * Checks if a file is of a certain type
     *
     * @param string $value An extension or mime type
     * @return bool
     */
    public function is(string $value): bool
    {
        return F::is($this->root, $value);
    }

    /**
     * Checks if the file is readable
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        return is_readable($this->root) === true;
    }

    /**
     * Checks if the file is writable
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        return F::isWritable($this->root);
    }

    /**
     * Detects the mime type of the file
     *
     * @return string|null
     */
    public function mime()
    {
        return Mime::type($this->root);
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $format
     * @param string $handler date or strftime
     * @return mixed
     */
    public function modified(string $format = null, string $handler = 'date')
    {
        return F::modified($this->root, $format, $handler);
    }

    /**
     * Move the file to a new location
     *
     * @param string $newRoot
     * @param bool $overwrite Force overwriting any existing files
     * @return self
     */
    public function move(string $newRoot, bool $overwrite = false)
    {
        if (F::move($this->root, $newRoot, $overwrite) !== true) {
            throw new Exception('The file: "' . $this->root . '" could not be moved to: "' . $newRoot . '"');
        }

        return new static($newRoot);
    }

    /**
     * Getter for the name of the file
     * without the extension
     *
     * @return string
     */
    public function name(): string
    {
        return pathinfo($this->root, PATHINFO_FILENAME);
    }

    /**
     * Returns the file size in a
     * human-readable format
     *
     * @return string
     */
    public function niceSize(): string
    {
        return F::niceSize($this->root);
    }

    /**
     * Reads the file content and returns it.
     *
     * @return string|false
     */
    public function read()
    {
        return F::read($this->root);
    }

    /**
     * Returns the absolute path to the file
     *
     * @return string
     */
    public function realpath(): string
    {
        return realpath($this->root);
    }

    /**
     * Changes the name of the file without
     * touching the extension
     *
     * @param string $newName
     * @param bool $overwrite Force overwrite existing files
     * @return self
     */
    public function rename(string $newName, bool $overwrite = false)
    {
        $newRoot = F::rename($this->root, $newName, $overwrite);

        if ($newRoot === false) {
            throw new Exception('The file: "' . $this->root . '" could not be renamed to: "' . $newName . '"');
        }

        return new static($newRoot);
    }

    /**
     * Returns the given file path
     *
     * @return string|null
     */
    public function root(): ?string
    {
        return $this->root;
    }

    /**
     * Returns the raw size of the file
     *
     * @return int
     */
    public function size(): int
    {
        return F::size($this->root);
    }

    /**
     * Converts the media object to a
     * plain PHP array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'root'         => $this->root(),
            'hash'         => $this->hash(),
            'filename'     => $this->filename(),
            'name'         => $this->name(),
            'safeName'     => F::safeName($this->name()),
            'extension'    => $this->extension(),
            'size'         => $this->size(),
            'niceSize'     => $this->niceSize(),
            'modified'     => $this->modified('c'),
            'mime'         => $this->mime(),
            'type'         => $this->type(),
            'isWritable'   => $this->isWritable(),
            'isReadable'   => $this->isReadable(),
        ];
    }

    /**
     * Returns the file type.
     *
     * @return string|false
     */
    public function type()
    {
        return F::type($this->root);
    }

    /**
     * Writes content to the file
     *
     * @param string $content
     * @return bool
     */
    public function write($content): bool
    {
        if (F::write($this->root, $content) !== true) {
            throw new Exception('The file "' . $this->root . '" could not be written');
        }

        return true;
    }
}
