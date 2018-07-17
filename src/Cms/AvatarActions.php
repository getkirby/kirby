<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\F;

trait AvatarActions
{

    /**
     * Commits a avatar action, by following these steps
     *
     * 1. checks the action rules
     * 2. sends the before hook
     * 3. commits the action callback
     * 4. sends the after hook
     * 5. returns the result
     *
     * @param string $action
     * @param mixed ...$arguments
     * @param Closure $callback
     * @return mixed
     */
    protected function commit(string $action, $arguments = [], Closure $callback)
    {
        $old = $this->hardcopy();

        $this->kirby()->trigger('avatar.' . $action . ':before', ...$arguments);
        $result = $callback(...$arguments);
        $this->kirby()->trigger('avatar.' . $action . ':after', $result, $old);
        return $result;
    }

    /**
     * Creates the avatar on upload.
     * The file system handling is done
     * by the store.
     *
     * @param array $props
     * @return self
     */
    public static function create(array $props): self
    {
        if (isset($props['source'], $props['user']) === false) {
            throw new InvalidArgumentException('Please provide the "source" and "user" props for the Avatar');
        }

        // create the basic avatar and a test upload object
        $avatar = new static($props);
        $upload = new Upload($props['source']);

        // validate the uploaded mime type
        if ($upload->mime() !== 'image/jpeg') {
            throw new InvalidArgumentException([
                'key' => 'avatar.mime.invalid',
            ]);
        }

        return $avatar->commit('create', [$avatar, $upload], function ($avatar, $upload) {

            // delete all public versions
            $avatar->unpublish();

            // overwrite the original
            if (F::copy($upload->root(), $avatar->root(), true) !== true) {
                throw new Exception([
                    'key' => 'avatar.create.fail',
                ]);
            }

            // return a fresh clone
            return $avatar->clone();
        });
    }

    /**
     * Deletes the avatar from the file system.
     * This is handled by the store.
     *
     * @return boolean
     */
    public function delete(): bool
    {
        return $this->commit('delete', [$this], function ($avatar) {
            if ($avatar->exists() === false) {
                return true;
            }

            // delete all public versions
            $avatar->unpublish();

            if (F::remove($avatar->root()) !== true) {
                throw new Exception([
                    'key' => 'avatar.delete.fail',
                ]);
            }

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
     * Replaces the avatar file with a new one.
     * This is handled by the store.
     *
     * @param string $source
     * @return self
     */
    public function replace(string $source): self
    {
        $upload = new Upload($source);

        // validate the uploaded mime type
        if ($upload->mime() !== 'image/jpeg') {
            throw new InvalidArgumentException([
                'key' => 'avatar.mime.invalid',
            ]);
        }

        return $this->commit('replace', [$this, $upload], function ($avatar, $upload) {

            // delete all public versions
            $avatar->unpublish();

            // overwrite the original
            if (F::copy($upload->root(), $avatar->root(), true) !== true) {
                throw new Exception([
                    'key' => 'avatar.replace.fail',
                ]);
            }

            // return a fresh clone
            return $avatar->clone();
        });
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
