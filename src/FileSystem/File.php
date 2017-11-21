<?php

namespace Kirby\FileSystem;

use Exception;
use Kirby\FileSystem\File\MimeType;
use Kirby\Toolkit\Str;

/**
 * Flexible File object with a set of helpful
 * methods to inspect and work with files.
 *
 * @package   Kirby FileSystem
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class File
{

    /**
     * File Types
     *
     * @var array
     */
    protected static $types = [
        'image' => [
            'jpeg',
            'jpg',
            'jpe',
            'gif',
            'png',
            'svg',
            'ico',
            'tif',
            'tiff',
            'bmp',
            'psd',
            'ai',
            'eps',
            'ps'
        ],
        'document' => [
            'txt',
            'text',
            'mdown',
            'md',
            'markdown',
            'pdf',
            'doc',
            'docx',
            'dotx',
            'word',
            'xl',
            'xls',
            'xlsx',
            'xltx',
            'ppt',
            'pptx',
            'potx',
            'csv',
            'rtf',
            'rtx',
            'log',
            'odt',
            'odp',
            'odc',
        ],
        'archive' => [
            'zip',
            'tar',
            'gz',
            'gzip',
            'tgz',
        ],
        'code' => [
            'js',
            'css',
            'scss',
            'htm',
            'html',
            'shtml',
            'xhtml',
            'php',
            'php3',
            'php4',
            'rb',
            'xml',
            'json',
            'java',
            'py'
        ],
        'video' => [
            'mov',
            'movie',
            'avi',
            'ogg',
            'ogv',
            'webm',
            'flv',
            'swf',
            'mp4',
            'm4v',
            'mpg',
            'mpe'
        ],
        'audio' => [
            'mp3',
            'm4a',
            'wav',
            'aif',
            'aiff',
            'midi',
        ],
    ];

    /**
     * Size units
     *
     * @var array
     */
    protected static $units = [
        'B',
        'kB',
        'MB',
        'GB',
        'TB',
        'PB',
        'EB',
        'ZB',
        'YB'
    ];

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
    public function __construct(string $root)
    {
        $this->root = $root;
    }

    /**
     * Returns the given file path
     *
     * @return string
     */
    public function root(): string
    {
        return $this->root;
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
     * Returns the parent folder object
     *
     * @return Folder
     */
    public function folder()
    {
        return new Folder(dirname($this->root));
    }

    /**
     * Checks if the file actually exists
     *
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->realpath()) === true;
    }

    /**
     * Checks if the file is readable
     *
     * @return boolean
     */
    public function isReadable(): bool
    {
        return is_readable($this->realpath()) === true;
    }

    /**
     * Checks if the file is writable
     *
     * @return boolean
     */
    public function isWritable(): bool
    {
        if ($this->exists() === false) {
            return $this->folder()->isWritable();
        } else {
            return is_writable($this->realpath()) === true;
        }
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
     * Sanitize a filename to strip unwanted special characters
     * (e.g. 'über genious.txt' will be 'ueber-genious.txt')
     *
     * @param  string  $name
     * @return string
     */
    public static function safeName(string $name): string
    {
        return Str::slug($name, '-', 'a-z0-9@._-');
    }

    /**
     * Returns the current lowercase extension (without .)
     *
     * @return string
     */
    public function extension(): string
    {
        return strtolower(pathinfo($this->root, PATHINFO_EXTENSION));
    }

    /**
     * Detects the mime type of the file
     *
     * @return string
     */
    public function mime(): string
    {
        return new MimeType($this);
    }

    /**
     * Returns the file type.
     *
     * @return string|false
     */
    public function type()
    {
        foreach (static::$types as $type => $extensions) {
            if (in_array($this->extension(), $extensions)) {
                return $type;
            }
        }

        return false;
    }

    /**
     * Returns the raw size of the file
     *
     * @return  int
     */
    public function size(): int
    {
        if ($this->exists() === false) {
            return 0;
        }

        return filesize($this->root);
    }

    /**
     * Returns the file size in a
     * human-readable format
     *
     * @return string
     */
    public function niceSize(): string
    {

        $size = $this->size();

        // avoid errors for invalid sizes
        if ($size <= 0) {
            return '0 ' . static::$units[0];
        }

        // the math magic
        return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . static::$units[$i];
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string $format
     * @param  string $handler date or strftime
     * @return mixed
     */
    public function modified(string $format = null, string $handler = 'date')
    {
        if ($this->exists()) {
            if (is_null($format) === true) {
                return filemtime($this->root());
            }
            return $handler($format, filemtime($this->root()));
        }
        return false;
    }

    /**
     * Writes content to the file
     *
     * @param  string $content
     * @return bool
     */
    public function write($content): bool
    {
        if ($this->isWritable() === false) {
            throw new Exception('The file is not writable');
        }

        if (file_put_contents($this->root, $content) === false) {
            throw new Exception('The file could not be written');
        }

        return true;
    }

    /**
     * Reads the file content and returns it.
     *
     * @return string
     */
    public function read()
    {
        if ($this->exists() === false) {
            throw new Exception('The file does not exist: ' . $this->root);
        }

        if ($this->isReadable() === false) {
            throw new Exception('The file is not readable: ' . $this->root);
        }

        return file_get_contents($this->root);
    }

    /**
     * Move the file to a new location
     *
     * @param  string $newRoot
     */
    public function move(string $newRoot)
    {
        if (file_exists($newRoot) === true) {
            throw new Exception('A file at the new location: "' . $newRoot . '" already exists.');
        }

        // check if the file exists
        if ($this->exists() !== true) {
            throw new Exception('The file does not exist');
        }

        // actually move the file if it exists
        if (rename($this->root(), $newRoot) !== true) {
            throw new Exception('The file: "' . $this->root . '" could not be moved to: "' . $newRoot . '"');
        }

        // assign the new root
        $this->root = $newRoot;

        return $this;
    }

    /**
     * Copy the file to a new location
     *
     * @param  string $newRoot
     * @return File
     */
    public function copy(string $newRoot): self
    {
        if (file_exists($newRoot) === true) {
            throw new Exception('A file at the new location: "' . $newRoot . '" already exists.');
        }

        // check if the file exists
        if ($this->exists() !== true) {
            throw new Exception('The file does not exist');
        }

        // actually move the file if it exists
        if (copy($this->root(), $newRoot) !== true) {
            throw new Exception('The file: "' . $this->root . '" could not be copied to: "' . $newRoot . '"');
        }

        return new self($newRoot);
    }

    /**
     * Changes the name of the file without
     * touching the extension
     *
     * @param  string $newName
     * @return File
     */
    public function rename(string $newName)
    {
        $clone = clone $this;

        // create the new name
        $name = static::safeName(basename($newName));

        // overwrite the root
        $clone->root = rtrim(dirname($this->root()) . '/' . $name . '.' . $this->extension(), '.');

        // nothing has changed
        if ($clone->root() === $this->root()) {
            return $this;
        }

        // move the file to the new root
        if ($this->exists()) {
            $this->move($clone->root());
        }

        return $clone;
    }

    /**
     * Deletes the file
     *
     * @return bool
     */
    public function delete(): bool
    {
        if ($this->exists() === false) {
            return true;
        }

        if (unlink($this->root()) !== true) {
            throw new Exception('The file "' . $this->root() . '" could not be deleted');
        }

        return true;
    }
}
