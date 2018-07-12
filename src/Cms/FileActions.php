<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Image\Image;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

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
            throw new InvalidArgumentException('Please provide the "source" and "parent" props for the File');
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
     * Move the file to the public media folder
     * if it's not already there.
     *
     * @return self
     */
    public function publish(): self
    {
        F::copy($this->root(), $this->mediaRoot());
        return $this;
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
     * Remove all public versions of this file
     *
     * @return self
     */
    public function unpublish(): self
    {
        // delete all thumbnails
        foreach (F::similar($this->mediaRoot(), '-*') as $similar) {
            F::remove($similar);
        }

        F::remove($this->mediaRoot());

        return $this;
    }
}
