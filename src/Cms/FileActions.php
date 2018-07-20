<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
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

        return $this->commit('changeName', [$this, $name], function ($oldFile, $name) {
            $newFile = $oldFile->clone([
                'filename' => $name . '.' . $oldFile->extension()
            ]);

            if ($oldFile->exists() === false) {
                return $newFile;
            }

            if ($newFile->exists() === true) {
                throw new LogicException('The new file exists and cannot be overwritten');
            }

            // remove all public versions
            $oldFile->unpublish();

            // rename the main file
            F::move($oldFile->root(), $newFile->root());

            // rename the content file
            F::move($oldFile->contentFile(), $newFile->contentFile());

            // create a new public version
            $newFile->publish();

            return $newFile;
        });
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
    protected function commit(string $action, array $arguments, Closure $callback)
    {
        $old   = $this->hardcopy();
        $kirby = $this->kirby();

        $this->rules()->$action(...$arguments);
        $kirby->trigger('file.' . $action . ':before', ...$arguments);
        $result = $callback(...$arguments);
        $kirby->trigger('file.' . $action . ':after', $result, $old);
        $kirby->cache('pages')->flush();
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

        return $file->commit('create', [$file, $upload], function ($file, $upload) {

            // delete all public versions
            $file->unpublish();

            // overwrite the original
            if (F::copy($upload->root(), $file->root(), true) !== true) {
                throw new LogicException('The file could not be created');
            }

            // store the content if necessary
            $file->save();

            // create a new public file
            $file->publish();

            // return a fresh clone
            return $file->clone();
        });
    }

    /**
     * Deletes the file. The store is used to
     * manipulate the filesystem or whatever you prefer.
     *
     * @return bool
     */
    public function delete(): bool
    {
        return $this->commit('delete', [$this], function ($file) {
            $file->unpublish();

            F::remove($file->contentFile());
            F::remove($file->root());

            return true;
        });
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
        return $this->commit('replace', [$this, new Upload($source)], function ($file, $upload) {

            // delete all public versions
            $file->unpublish();

            // overwrite the original
            if (F::copy($upload->root(), $file->root(), true) !== true) {
                throw new LogicException('The file could not be created');
            }

            // create a new public file
            $file->publish();

            // return a fresh clone
            return $file->clone();
        });
    }

    /**
     * Stores the file meta content on disk
     *
     * @return self
     */
    public function save(): self
    {
        if ($this->exists() === false) {
            return $this;
        }

        $content = $this->content()->toArray();

        // store main information in the content file
        $content['template'] = $this->template();

        Data::write($this->contentFile(), $content);

        return $this;
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

    /**
     * Updates the file data
     *
     * @param array $input
     * @param boolean $validate
     * @return self
     */
    public function update(array $input = null, bool $validate = true): self
    {
        $form = Form::for($this, [
            'values' => $input
        ]);

        // validate the input
        if ($validate === true) {
            if ($form->isInvalid() === true) {
                throw new InvalidArgumentException([
                    'fallback' => 'Invalid form with errors',
                    'details'  => $form->errors()
                ]);
            }
        }

        return $this->commit('update', [$this, $form->values(), $form->strings()], function ($file, $values, $strings) {
            $content = $file
                ->content()
                ->update($strings)
                ->toArray();

            return $file->clone(['content' => $content])->save();
        });
    }
}
