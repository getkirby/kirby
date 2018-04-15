<?php

namespace Kirby\FileSystem\File;

use Exception;
use Kirby\FileSystem\File;
use SimpleXMLElement;

/**
 * Extensive MimeType detection
 *
 * @package   Kirby FileSystem
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class MimeType
{

    /**
     * The parent file object
     *
     * @var File
     */
    protected $file;

    /**
     * The name of the mime type
     * i.e. image/jpeg
     *
     * @var string
     */
    protected $name;

    /**
     * Initializes a new MimeType object
     *
     * @param string|File $file
     */
    public function __construct($file)
    {
        if (is_a($file, File::class) === false) {
            $file = new File($file);
        }

        if ($file->exists() === false) {
            throw new Exception('File does not exist');
        }

        $this->file = $file;
        $this->name = $this->getFromFileInfo();

        if (!$this->name) {
            $this->name = $this->getFromMimeContentType();
        }

        if (!$this->name) {
            $this->name = $this->getFromSystem();
        }

        if (!$this->name) {
            $this->name = $this->getFromExtension();
        }

        if (!$this->name) {
            throw new Exception('The mime type for "' . $this->file->root() . '" could not be detected');
        }

        if ($this->isSvg()) {
            $this->name = 'image/svg+xml';
        }
    }

    /**
     * Tries to detect the mime type with
     * the finfo extension
     *
     * @return string|false
     */
    protected function getFromFileInfo()
    {
        if (function_exists('finfo_file') === false) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $this->file->realpath());
        finfo_close($finfo);

        return $mime;
    }

    /**
     * Tries to detect the mime type with
     * the mime_content_type function
     *
     * @return string|false
     */
    protected function getFromMimeContentType()
    {
        if (function_exists('mime_content_type') === false) {
            return false;
        }

        return mime_content_type($this->file->realpath());
    }

    /**
     * Tries to detect the mime type via system command
     *
     * @return string|false
     */
    protected function getFromSystem(): string
    {
        throw new Exception('Not yet implemented');
    }

    /**
     * Tries to guess the mime type from the extension
     *
     * @return string|false
     */
    protected function getFromExtension(): string
    {
        throw new Exception('Not yet implemented');
    }

    /**
     * Checks if the file is actually an SVG
     * Mime detectors often return text/html instead
     *
     * @return boolean
     */
    public function isSvg(): bool
    {
        if ($this->name === 'text/html' || $this->file->extension() === 'svg') {
            libxml_use_internal_errors(true);

            try {
                $svg = new SimpleXMLElement($this->file->read());

                if ($svg !== false && $svg->getName() === 'svg') {
                    return true;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Returns the name of the detected mime type
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Converts the MimeType object to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
