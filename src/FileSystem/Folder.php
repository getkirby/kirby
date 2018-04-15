<?php

namespace Kirby\FileSystem;

use Exception;

/**
 * Flexible Folder object to
 * inspect and work with folders in
 * the file system
 *
 * @package   Kirby FileSystem
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Folder
{

    /**
     * Array of filenames, which should
     * be skipped when scanning a folder
     *
     * @var array
     */
    protected static $ignore = [
        '.',
        '..',
        '.DS_Store',
        '.gitignore',
        '.git',
        '.svn',
        '.htaccess',
        'Thumb.db',
        '@eaDir'
    ];

    /**
     * The absolute folder path
     *
     * @var string
     */
    protected $root;

    /**
     * Store for scanned files.
     *
     * @var array
     */
    protected $files = [];

    /**
     * Store for scanned subfolders
     *
     * @var array
     */
    protected $folders = [];

    /**
     * Flag if the Folder contents have
     * been scanned already
     *
     * @var boolean
     */
    protected $isScanned = false;

    /**
     * Register a set of filenames to be ignored
     * when scanning the folder. Can also be used
     * as getter to return the array of ignored
     * filenames, which will be applied.
     *
     * @param  array|null $ignore Pass an array of filenames to use this as setter.
     *                            Pass nothing to use as getter.
     * @return array              The array of ignored filenames
     */
    public static function ignore(array $ignore = null): array
    {
        if ($ignore === null) {
            return static::$ignore;
        } else {
            return static::$ignore = $ignore;
        }
    }

    /**
     * Creates a new Folder object
     *
     * @param string $root Absolute folder path
     */
    public function __construct(string $root)
    {
        $this->root = $root;
    }

    /**
     * Creates the folder if it does not exist yet
     *
     * @param boolean $recursive
     * @return boolean
     */
    public function make(bool $recursive = false): bool
    {
        if ($this->exists()) {
            return true;
        }

        if ($recursive === true) {
            if ($this->parent()->exists() === false) {
                $this->parent()->make(true);
            }
        }

        if (is_writable(dirname($this->root())) === false) {
            throw new Exception(sprintf('The folder "%s" cannot be created', $this->root()));
        }

        return mkdir($this->root());
    }

    /**
     * Internal method to scan the folder contents
     * Will be used by Folder::folders() and Folder::files()
     *
     * @return array
     */
    protected function scan(): array
    {
        if ($this->isScanned !== true && $this->exists()) {
            foreach (scandir($this->root) as $item) {
                if (!in_array($item, static::$ignore)) {
                    $root = $this->root . '/' . $item;
                    if (is_dir($root)) {
                        $this->folders[$item] = $root;
                    } else {
                        $this->files[$item] = $root;
                    }
                }
            }

            $this->isScanned = true;
        }

        return [
            'folders' => $this->folders,
            'files'   => $this->files
        ];
    }

    /**
     * Returns the absolute folder path
     *
     * @return string
     */
    public function root(): string
    {
        return $this->root;
    }

    /**
     * Checks if the folder actually exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return is_dir($this->root) === true;
    }

    /**
     * Checks if the folder is readable
     *
     * @return boolean
     */
    public function isReadable(): bool
    {
        return is_readable($this->root) === true;
    }

    /**
     * Checks if the folder is writable
     *
     * @return boolean
     */
    public function isWritable(): bool
    {
        return is_writable($this->root) === true;
    }

    /**
     * Returns the base name of the folder
     *
     * @return string
     */
    public function name(): string
    {
        return basename($this->root);
    }

    /**
     * Returns a File object for a given filename
     * within the folder
     *
     * @param  string $filename
     * @return File
     */
    public function file(string $filename)
    {
        return new File($this->root . '/' . $filename);
    }

    /**
     * Returns an array of all files in the folder
     *
     * @return array
     */
    public function files(): array
    {
        $this->scan();
        return $this->files;
    }

    /**
     * Returns a Folder object for a subfolder
     *
     * @param  string $folder The name of the subfolder
     * @return Folder
     */
    public function folder(string $folder): Folder
    {
        return new static($this->root . '/' . basename($folder));
    }

    /**
     * Returns an array of all subfolders within the folder.
     *
     * @return array
     */
    public function folders(): array
    {
        $this->scan();
        return $this->folders;
    }

    /**
     * Deletes the folder and all its contents
     *
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->exists() === false) {
            return true;
        }

        $ignore = ['.', '..'];

        foreach (scandir($this->root) as $item) {
            if (in_array($item, $ignore)) {
                continue;
            }

            $root = $this->root . '/' . $item;

            if (is_dir($root)) {
                $subfolder = new static($root);
                $subfolder->delete();
            } else {
                $file = new File($root);
                $file->delete();
            }
        }

        return rmdir($this->root);
    }

    /**
     * Returns the parent folder object
     *
     * @return Folder
     */
    public function parent()
    {
        if ($this->root === '/') {
            return null;
        } else {
            return new static(dirname($this->root));
        }
    }

    /**
     * Runs a glob search within the folder
     *
     * @param  string $glob
     * @return array
     */
    public function find(string $glob): array
    {
        return (array)glob($this->root . '/' . $glob);
    }

    /**
     * Returns the absolute path of the folder
     * Can be used to convert the Folder object to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->root;
    }
}
