<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Image\Image;
use Kirby\Toolkit\F;

/**
 * FileActions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
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
    public function changeName(string $name, bool $sanitize = true)
    {
        if ($sanitize === true) {
            $name = F::safeName($name);
        }

        // don't rename if not necessary
        if ($name === $this->name()) {
            return $this;
        }

        return $this->commit('changeName', ['file' => $this, 'name' => $name], function ($oldFile, $name) {
            $newFile = $oldFile->clone([
                'filename' => $name . '.' . $oldFile->extension(),
            ]);

            if ($oldFile->exists() === false) {
                return $newFile;
            }

            if ($newFile->exists() === true) {
                throw new LogicException('The new file exists and cannot be overwritten');
            }

            // remove the lock of the old file
            if ($lock = $oldFile->lock()) {
                $lock->remove();
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
     * @param int $sort
     * @return self
     */
    public function changeSort(int $sort)
    {
        return $this->commit('changeSort', ['file' => $this, 'position' => $sort], function ($file, $sort) {
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
        $old            = $this->hardcopy();
        $kirby          = $this->kirby();
        $argumentValues = array_values($arguments);

        $this->rules()->$action(...$argumentValues);
        $kirby->trigger('file.' . $action . ':before', $arguments);

        $result = $callback(...$argumentValues);

        if ($action === 'create') {
            $argumentsAfter = ['file' => $result];
        } elseif ($action === 'delete') {
            $argumentsAfter = ['status' => $result, 'file' => $old];
        } else {
            $argumentsAfter = ['newFile' => $result, 'oldFile' => $old];
        }
        $kirby->trigger('file.' . $action . ':after', $argumentsAfter);

        $kirby->cache('pages')->flush();
        return $result;
    }

    /**
     * Copy the file to the given page
     *
     * @param \Kirby\Cms\Page $page
     * @return \Kirby\Cms\File
     */
    public function copy(Page $page)
    {
        F::copy($this->root(), $page->root() . '/' . $this->filename());

        if ($this->kirby()->multilang() === true) {
            foreach ($this->kirby()->languages() as $language) {
                $contentFile = $this->contentFile($language->code());
                F::copy($contentFile, $page->root() . '/' . basename($contentFile));
            }
        } else {
            $contentFile = $this->contentFile();
            F::copy($contentFile, $page->root() . '/' . basename($contentFile));
        }

        return $page->clone()->file($this->filename());
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
    public static function create(array $props)
    {
        if (isset($props['source'], $props['parent']) === false) {
            throw new InvalidArgumentException('Please provide the "source" and "parent" props for the File');
        }

        // prefer the filename from the props
        $props['filename'] = F::safeName($props['filename'] ?? basename($props['source']));

        $props['model'] = strtolower($props['template'] ?? 'default');

        // create the basic file and a test upload object
        $file = static::factory($props);
        $upload = new Image($props['source']);

        // create a form for the file
        $form = Form::for($file, [
            'values' => $props['content'] ?? []
        ]);

        // inject the content
        $file = $file->clone(['content' => $form->strings(true)]);

        // run the hook
        return $file->commit('create', compact('file', 'upload'), function ($file, $upload) {

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
        return $this->commit('delete', ['file' => $this], function ($file) {

            // remove all versions in the media folder
            $file->unpublish();

            // remove the lock of the old file
            if ($lock = $file->lock()) {
                $lock->remove();
            }

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
    public function publish()
    {
        Media::publish($this, $this->mediaRoot());
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
        deprecated('$file->rename() is deprecated, use $file->changeName() instead. $file->rename() will be removed in Kirby 3.5.0.');

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
    public function replace(string $source)
    {
        return $this->commit('replace', ['file' => $this, 'upload' => new Image($source)], function ($file, $upload) {

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
    public function unpublish()
    {
        Media::unpublish($this->parent()->mediaRoot(), $this);
        return $this;
    }
}
