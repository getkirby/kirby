<?php

namespace Kirby\Cms;

trait HasThumbs
{

    /**
     * The original object
     * before manipulations
     *
     * @var File|Avatar
     */
    protected $original;

    public function crop(int $width, int $height = null, $options = null)
    {
        $quality = null;
        $crop    = 'center';

        if (is_int($options) === true) {
            $quality = $options;
        } elseif (is_string($options)) {
            $crop = $options;
        } elseif (is_array($options)) {
            $quality = $options['quality'] ?? $quality;
            $crop    = $options['crop']    ?? $crop;
        }

        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality,
            'crop'    => $crop
        ]);
    }

    /**
     * Returns the original object
     *
     * @return File|Avatar
     */
    public function original()
    {
        return $this->original;
    }

    public function resize(int $width = null, int $height = null, int $quality = null)
    {
        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality
        ]);
    }

    /**
     * Sets the original object
     * before a file has been modified
     *
     * @param File|Avatar $original
     * @return self
     */
    protected function setOriginal(Model $original = null): self
    {
        $this->original = $original;
        return $this;
    }
}
