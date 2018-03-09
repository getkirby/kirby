<?php

namespace Kirby\Cms;

use Exception;

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
        $this->rules()->$action($this, ...$arguments);
        $this->kirby()->trigger('avatar.' . $action . ':before', $this, ...$arguments);
        $result = $this->store()->$action(...$arguments);
        $this->kirby()->trigger('avatar.' . $action . ':after', $result, $this);
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
            throw new Exception('Please provide the "source" and "user" props for the Avatar');
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
     * Main thumb generation method.
     * This is also reused by crop and resize
     * methods in the HasThumbs trait.
     *
     * @param array $options
     * @return self
     */
    public function thumb(array $options = []): self
    {
        return $this->store()->thumb($options);
    }

}
