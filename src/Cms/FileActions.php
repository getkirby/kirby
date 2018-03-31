<?php

namespace Kirby\Cms;

use Kirby\Image\Image;
use Kirby\Util\Str;

use Kirby\Exception\InvalidArgumentException;

trait FileActions
{

    /**
     * Renames the file without touching the extension
     * The store is used to actually execute this.
     *
     * @param string $name
     * @param bool $sanitize
     * @return self
     */
    public function changeName(string $name, bool $sanitize = true): self
    {
        if ($sanitize === true) {
            $name = Str::slug($name);
        }

        // don't rename if not necessary
        if ($name === $this->name()) {
            return $this;
        }

        return $this->commit('changeName', $name);
    }

    /**
     * @param integer $num
     * @return self
     */
    public function changeSort(int $num): self
    {
        return $this->commit('changeSort', $num);
    }

    /**
     * Commits a file action, by following these steps
     *
     * 1. checks the action rules
     * 2. sends the before hook
     * 3. commits the store action
     * 4. sends the after hook
     * 5. returns the result
     *
     * @param string $action
     * @param mixed ...$arguments
     * @return mixed
     */
    protected function commit(string $action, ...$arguments)
    {
        $old = $this->hardcopy();

        $this->rules()->$action($this, ...$arguments);
        $this->kirby()->trigger('file.' . $action . ':before', $this, ...$arguments);
        $result = $this->store()->$action(...$arguments);
        $this->kirby()->trigger('file.' . $action . ':after', $result, $old);
        $this->kirby()->cache('pages')->flush();
        return $result;
    }

    /**
     * Creates a new file on disk and returns the
     * File object. The store is used to handle file
     * writing, so it can be replaced by any other
     * way of generating files.
     *
     * @param array $props
     * @return self
     */
    public static function create(array $props): self
    {
        if (isset($props['source'], $props['parent']) === false) {
            throw new InvalidArgumentException([
                'key' => 'file.props.missing',
            ]);
        }

        // prefer the filename from the props
        $props['filename'] = $props['filename'] ?? basename($props['source']);

        // create the basic file and a test upload object
        $file   = new static($props);
        $upload = new Upload($props['source']);

        return $file->commit('create', $upload);
    }

    /**
     * Deletes the file. The store is used to
     * manipulate the filesystem or whatever you prefer.
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->commit('delete');
    }

    /**
     * Replaces the file. The source must
     * be an absolute path to a file or a Url.
     * The store handles the replacement so it
     * finally decides what it will support as
     * source.
     *
     * @param string $source
     * @return self
     */
    public function replace(string $source): self
    {
        return $this->commit('replace', new Upload($source));
    }

    /**
     * Creates a modified version of images
     * The media manager takes care of generating
     * those modified versions and putting them
     * in the right place. This is normally the
     * /media folder of your installation, but
     * could potentially also be a CDN or any other
     * place.
     *
     * @param array $options
     * @return self
     */
    public function thumb(array $options = []): self
    {
        $media = $this->kirby()->media();

        try {
            if ($this->page() === null) {
                return $media->create($this->site(), $this, $options);
            }

            return $media->create($this->page(), $this, $options);
        } catch (Exception $e) {
            return $this;
        }
    }

}
