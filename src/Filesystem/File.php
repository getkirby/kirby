<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Http\Response;
use Kirby\Sane\Sane;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\V;

/**
 * Flexible File object with a set of helpful
 * methods to inspect and work with files.
 *
 * @since 3.6.0
 *
 * @package   Kirby Filesystem
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class File
{
    use Properties;

    /**
     * Absolute file path
     *
     * @var string
     */
    protected $root;

    /**
     * Absolute file URL
     *
     * @var string|null
     */
    protected $url;

    /**
     * Validation rules to be used for `::match()`
     *
     * @var array
     */
    public static $validations = [
        'maxsize' => ['size', 'max'],
        'minsize' => ['size', 'min']
    ];

    /**
     * Constructor sets all file properties
     *
     * @param array|string|null $props Properties or deprecated `$root` string
     * @param string|null $url Deprecated argument, use `$props['url']` instead
     */
    public function __construct($props = null, string $url = null)
    {
        // Legacy support for old constructor of
        // the `Kirby\Image\Image` class
        // @todo 4.0.0 remove
        if (is_array($props) === false) {
            $props = [
                'root' => $props,
                'url'  => $url
            ];
        }

        $this->setProperties($props);
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
     * Returns the URL for the file object
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->url() ?? $this->root() ?? '';
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
     * @return static
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

    /*
     * Automatically sends all needed headers for the file to be downloaded
     * and echos the file's content
     *
     * @param string|null $filename Optional filename for the download
     * @return string
     */
    public function download($filename = null): string
    {
        return Response::download($this->root, $filename ?? $this->filename());
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
     * Sends an appropriate header for the asset
     *
     * @param bool $send
     * @return \Kirby\Http\Response|void
     */
    public function header(bool $send = true)
    {
        $response = new Response('', $this->mime());

        if ($send !== true) {
            return $response;
        }

        $response->send();
    }

    /**
     * Converts the file to html
     *
     * @param array $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        return Html::a($this->url() ?? '', $attr);
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
     * Checks if the file is a resizable image
     *
     * @return bool
     */
    public function isResizable(): bool
    {
        return false;
    }

    /**
     * Checks if a preview can be displayed for the file
     * in the panel or in the frontend
     *
     * @return bool
     */
    public function isViewable(): bool
    {
        return false;
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
     * Returns the app instance if it exists
     *
     * @return \Kirby\Cms\App|null
     */
    public function kirby()
    {
        return App::instance(null, true);
    }

    /**
     * Runs a set of validations on the file object
     * (mainly for images).
     *
     * @param array $rules
     * @return bool
     * @throws \Kirby\Exception\Exception
     */
    public function match(array $rules): bool
    {
        $rules = array_change_key_case($rules);

        if (is_array($rules['mime'] ?? null) === true) {
            $mime = $this->mime();

            // determine if any pattern matches the MIME type;
            // once any pattern matches, `$carry` is `true` and the rest is skipped
            $matches = array_reduce($rules['mime'], function ($carry, $pattern) use ($mime) {
                return $carry || Mime::matches($mime, $pattern);
            }, false);

            if ($matches !== true) {
                throw new Exception([
                    'key'  => 'file.mime.invalid',
                    'data' => compact('mime')
                ]);
            }
        }

        if (is_array($rules['extension'] ?? null) === true) {
            $extension = $this->extension();
            if (in_array($extension, $rules['extension']) !== true) {
                throw new Exception([
                    'key'  => 'file.extension.invalid',
                    'data' => compact('extension')
                ]);
            }
        }

        if (is_array($rules['type'] ?? null) === true) {
            $type = $this->type();
            if (in_array($type, $rules['type']) !== true) {
                throw new Exception([
                    'key'  => 'file.type.invalid',
                    'data' => compact('type')
                ]);
            }
        }

        foreach (static::$validations as $key => $arguments) {
            $rule = $rules[$key] ?? null;

            if ($rule !== null) {
                $property  = $arguments[0];
                $validator = $arguments[1];

                if (V::$validator($this->$property(), $rule) === false) {
                    throw new Exception([
                        'key'  => 'file.' . $key,
                        'data' => [$property => $rule]
                    ]);
                }
            }
        }

        return true;
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
     * Returns the file's last modification time
     *
     * @param string $format
     * @param string|null $handler date or strftime
     * @return mixed
     */
    public function modified(?string $format = null, ?string $handler = null)
    {
        $kirby = $this->kirby();

        return F::modified(
            $this->root,
            $format,
            $handler ?? ($kirby ? $kirby->option('date.handler', 'date') : 'date')
        );
    }

    /**
     * Move the file to a new location
     *
     * @param string $newRoot
     * @param bool $overwrite Force overwriting any existing files
     * @return static
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
     * @return static
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
     * Setter for the root
     *
     * @param string|null $root
     * @return $this
     */
    protected function setRoot(?string $root = null)
    {
        $this->root = $root;
        return $this;
    }

    /**
     * Setter for the file url
     *
     * @param string|null $url
     * @return $this
     */
    protected function setUrl(?string $url = null)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Returns the absolute url for the file
     *
     * @return string|null
     */
    public function url(): ?string
    {
        return $this->url;
    }

    /**
     * Sanitizes the file contents depending on the file type
     * by overwriting the file with the sanitized version
     * @since 3.6.0
     *
     * @param string|bool $typeLazy Explicit sane handler type string,
     *                              `true` for lazy autodetection or
     *                              `false` for normal autodetection
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     * @throws \Kirby\Exception\LogicException If more than one handler applies
     * @throws \Kirby\Exception\NotFoundException If the handler was not found
     * @throws \Kirby\Exception\Exception On other errors
     */
    public function sanitizeContents($typeLazy = false): void
    {
        Sane::sanitizeFile($this->root(), $typeLazy);
    }

    /**
     * Returns the sha1 hash of the file
     * @since 3.6.0
     *
     * @return string
     */
    public function sha1(): string
    {
        return sha1_file($this->root);
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
            'extension'    => $this->extension(),
            'filename'     => $this->filename(),
            'hash'         => $this->hash(),
            'isReadable'   => $this->isReadable(),
            'isResizable'  => $this->isResizable(),
            'isWritable'   => $this->isWritable(),
            'mime'         => $this->mime(),
            'modified'     => $this->modified('c'),
            'name'         => $this->name(),
            'niceSize'     => $this->niceSize(),
            'root'         => $this->root(),
            'safeName'     => F::safeName($this->name()),
            'size'         => $this->size(),
            'type'         => $this->type(),
            'url'          => $this->url()
        ];
    }

    /**
     * Converts the entire file array into
     * a json string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Returns the file type.
     *
     * @return string|null
     */
    public function type(): ?string
    {
        return F::type($this->root);
    }

    /**
     * Validates the file contents depending on the file type
     *
     * @param string|bool $typeLazy Explicit sane handler type string,
     *                              `true` for lazy autodetection or
     *                              `false` for normal autodetection
     * @return void
     *
     * @throws \Kirby\Exception\InvalidArgumentException If the file didn't pass validation
     * @throws \Kirby\Exception\NotFoundException If the handler was not found
     * @throws \Kirby\Exception\Exception On other errors
     */
    public function validateContents($typeLazy = false): void
    {
        Sane::validateFile($this->root(), $typeLazy);
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
