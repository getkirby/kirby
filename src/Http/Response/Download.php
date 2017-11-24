<?php

namespace Kirby\Http\Response;

use Exception;
use Kirby\Http\Response;

/**
 * @package   Kirby Response
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Download extends Response
{

    /**
     * The name of the file, which should be
     * downloaded
     *
     * @var string
     */
    protected $filename;

    /**
     * The last modification date of the file
     *
     * @var int
     */
    protected $modified;

    /**
     * The file size in bytes
     *
     * @var int
     */
    protected $size;

    /**
     * Creates a new Download object
     *
     * @param string      $file
     * @param string|null $filename
     */
    public function __construct(string $file, string $filename = null)
    {
        $file = realpath($file);

        if (file_exists($file) === false) {
            throw new Exception('The file could not be found');
        }

        // set the filename
        $this->filename($filename === null ? basename($file) : $filename);

        $this->body(file_get_contents($file));
        $this->code(200);
        $this->modified(filemtime($file));
        $this->type('application/force-download');
    }

    /**
     * Setter and getter for the custom
     * filename for the downloaded file
     *
     * @param  string|null $filename
     * @return string
     */
    public function filename(string $filename = null): string
    {
        if ($filename === null) {
            return $this->filename;
        }

        return $this->filename = $filename;
    }

    /**
     * Setter and getter for the last
     * modification date of the file
     * to be downloaded
     *
     * @param  int|null $modified
     * @return int
     */
    public function modified(int $modified = null): int
    {
        if ($modified === null) {
            return $this->modified;
        }

        return $this->modified = $modified;
    }

    /**
     * Returns the filesize of the
     * download file in bytes
     *
     * @return int
     */
    public function size(): int
    {
        return strlen($this->body());
    }

    /**
     * Sends all header for the download
     * and returns the file content
     *
     * @return string
     */
    public function send(): string
    {
        $this->headers([
            'Pragma'                    => 'public',
            'Expires'                   => '0',
            'Last-Modified'             => gmdate('D, d M Y H:i:s', $this->modified()) . ' GMT',
            'Content-Disposition'       => 'attachment; filename="' . $this->filename() . '"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Length'            => $this->size(),
            'Connection'                => 'close'
        ]);

        return parent::send();
    }

    /**
     * Converts all class attributes
     * into a human readable array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'filename' => $this->filename(),
            'type'     => $this->type(),
            'charset'  => $this->charset(),
            'modified' => $this->modified(),
            'code'     => $this->code(),
            'body'     => $this->body()
        ];
    }
}
