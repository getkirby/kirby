<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

trait HasFiles
{

    /**
     * The Files collection
     *
     * @var Files
     */
    protected $files;

    /**
     * Filters the Files collection by type audio
     *
     * @return Files
     */
    public function audio(): Files
    {
        return $this->files()->filterBy('type', '==', 'audio');
    }

    /**
     * Filters the Files collection by type code
     *
     * @return Files
     */
    public function code(): Files
    {
        return $this->files()->filterBy('type', '==', 'code');
    }

    /**
     * Returns a list of file ids
     * for the toArray method of the model
     *
     * @return array
     */
    protected function convertFilesToArray(): array
    {
        return $this->files()->keys();
    }

    /**
     * Filters the Files collection by type documents
     *
     * @return Files
     */
    public function documents(): Files
    {
        return $this->files()->filterBy('type', '==', 'document');
    }

    /**
     * Returns a specific file by filename or the first one
     *
     * @param string $filename
     * @param string $in
     * @return File
     */
    public function file(string $filename = null, string $in = 'files')
    {
        if ($filename === null) {
            return $this->$in()->first();
        }

        if (Str::contains($filename, '/')) {
            $path     = dirname($filename);
            $filename = basename($filename);
            return $this->find($path)->$in()->find($filename);
        }

        return $this->$in()->find($filename);
    }

    /**
     * Returns the Files collection
     *
     * @return Files
     */
    public function files()
    {
        if (is_a($this->files, Files::class) === true) {
            return $this->files;
        }

        return $this->files = new Files([]);
    }

    /**
     * Checks if the Files collection has any audio files
     *
     * @return bool
     */
    public function hasAudio(): bool
    {
        return $this->audio()->count() > 0;
    }

    /**
     * Checks if the Files collection has any code files
     *
     * @return bool
     */
    public function hasCode(): bool
    {
        return $this->code()->count() > 0;
    }

    /**
     * Checks if the Files collection has any document files
     *
     * @return bool
     */
    public function hasDocuments(): bool
    {
        return $this->documents()->count() > 0;
    }

    /**
     * Checks if the Files collection has any files
     *
     * @return bool
     */
    public function hasFiles(): bool
    {
        return $this->files()->count() > 0;
    }

    /**
     * Checks if the Files collection has any images
     *
     * @return bool
     */
    public function hasImages(): bool
    {
        return $this->images()->count() > 0;
    }

    /**
     * Checks if the Files collection has any videos
     *
     * @return bool
     */
    public function hasVideos(): bool
    {
        return $this->videos()->count() > 0;
    }

    /**
     * Returns a specific image by filename or the first one
     *
     * @param string $filename
     * @return File
     */
    public function image(string $filename = null)
    {
        return $this->file($filename, 'images');
    }

    /**
     * Filters the Files collection by type image
     *
     * @return Files
     */
    public function images(): Files
    {
        return $this->files()->filterBy('type', '==', 'image');
    }

    /**
     * Sets the Files collection
     *
     * @param Files|null $files
     * @return self
     */
    protected function setFiles(array $files = null): self
    {
        $this->files = $files;
        return $this;
    }

    /**
     * Filters the Files collection by type videos
     *
     * @return Files
     */
    public function videos(): Files
    {
        return $this->files()->filterBy('type', '==', 'video');
    }
}
