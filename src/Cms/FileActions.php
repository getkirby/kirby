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
            $name = F::safeName($name);
        }

        // don't rename if not necessary
        if ($name === $this->name()) {
            return $this;
        }

        return $this->commit('changeName', [$this, $name], function ($oldFile, $name) {
            $newFile = $oldFile->clone([
                'filename' => $name . '.' . $oldFile->extension(),
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

            if ($newFile->kirby()->multilang() === true) {
                foreach ($newFile->translations() as $translation) {
                    $translationCode = $translation->code();

                    // rename the content file
                    F::move($oldFile->contentFile($translationCode), $newFile->contentFile($translationCode));
                }
            } else {
                // rename the content file
                F::move($oldFile->contentFile(), $newFile->contentFile());
            }


            return $newFile;
        });
    }

    /**
     * Changes the file's sorting number in the meta file
     *
     * @param integer $sort
     * @return self
     */
    public function changeSort(int $sort)
    {
        return $this->commit('changeSort', [$this, $sort], function ($file, $sort) {
            return $file->save(['sort' => $sort]);
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
     * @param array $arguments
     * @param Closure $callback
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
        $props['filename'] = F::safeName($props['filename'] ?? basename($props['source']));

        // create the basic file and a test upload object
        $file   = new static($props);
        $upload = new Image($props['source']);

        // create a form for the file
        $form = Form::for($file, [
            'values' => $props['content'] ?? []
        ]);

        // inject the content
        $file = $file->clone(['content' => $form->strings(true)]);

        // run the hook
        return $file->commit('create', [$file, $upload], function ($file, $upload) {

            // delete all public versions
            $file->unpublish();

            // overwrite the original
            if (F::copy($upload->root(), $file->root(), true) !== true) {
                throw new LogicException('The file could not be created');
            }

            // always create pages in the default language
            if ($file->kirby()->multilang() === true) {
                $languageCode = $file->kirby()->defaultLanguage()->code();
            } else {
                $languageCode = null;
            }

            // store the content if necessary
            $file->save($file->content()->toArray(), $languageCode);

            // add the file to the list of siblings
            $file->siblings()->append($file->id(), $file);

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

            if ($file->kirby()->multilang() === true) {
                foreach ($file->translations() as $translation) {
                    F::remove($file->contentFile($translation->code()));
                }
            } else {
                F::remove($file->contentFile());
            }

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
        Media::publish($this->root(), $this->mediaRoot());
        return $this;
    }

    /**
     * @deprecated 3.0.0 Use `File::changeName()` instead
     *
     * @param string $name
     * @param bool $sanitize
     * @return self
     */
    public function rename(string $name, bool $sanitize = true)
    {
        return $this->changeName($name, $sanitize);
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
        return $this->commit('replace', [$this, new Image($source)], function ($file, $upload) {

            // delete all public versions
            $file->unpublish();

            // overwrite the original
            if (F::copy($upload->root(), $file->root(), true) !== true) {
                throw new LogicException('The file could not be created');
            }

            // return a fresh clone
            return $file->clone();
        });
    }

    /**
     * Remove all public versions of this file
     *
     * @return self
     */
    public function unpublish(): self
    {
        Media::unpublish($this->parent()->mediaRoot(), $this->filename());
        return $this;
    }
}
