<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\F;

trait AvatarActions
{

    /**
     * Commits a avatar action, by following these steps
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
        $this->kirby()->trigger('avatar.' . $action . ':before', $this, ...$arguments);
        $result = $this->store()->$action(...$arguments);
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

        return $avatar->commit('create', $upload);
    }

    /**
     * Deletes the avatar from the file system.
     * This is handled by the store.
     *
     * @return boolean
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
        F::link($this->root(), $this->mediaRoot());
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
