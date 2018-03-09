<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Image\Image;

trait AvatarActions
{

    /**
     * Creates the avatar on upload.
     * The file system handling is done
     * by the store.
     *
     * @param User $user
     * @param string $source
     * @return self
     */
    public function create(string $source): self
    {
        // temporary image object to inspect the source
        $source = new Image($source);

        $this->rules()->create($this, $source);

        return $this->store()->create($source);
    }

    /**
     * Deletes the avatar from the file system.
     * This is handled by the store.
     *
     * @return boolean
     */
    public function delete(): bool
    {
        $this->rules()->delete($this);

        return $this->store()->delete();
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
        // temporary image object to inspect the source
        $source = new Image($source);

        $this->rules()->replace($this, $source);

        return $this->store()->replace($source);
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
