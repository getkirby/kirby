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
    protected $modifications = [];

    public function blur($pixels = true)
    {
        return $this->thumb(['blur' => $pixels]);
    }

    public function bw()
    {
        return $this->thumb(['grayscale' => true]);
    }

    public function crop(int $width, int $height = null, $options = null)
    {
        $quality = null;
        $crop    = 'center';

        if (is_int($options) === true) {
            $quality = $options;
        } elseif (is_string($options)) {
            $crop = $options;
        } elseif (is_a($options, 'Kirby\Cms\Field') === true) {
            $crop = $options->value();
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
     * Returns applied modifications after
     * thumbnail generation.
     *
     * @return array|null
     */
    public function modifications(): ?array
    {
        return $this->modifications;
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

    public function quality(int $quality)
    {
        return $this->thumb(['quality' => $quality]);
    }

    public function resize(int $width = null, int $height = null, int $quality = null)
    {
        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality
        ]);
    }

    protected function setModifications(array $modifications = []): self
    {
        $this->modifications = $modifications;
        return $this;
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

    /**
     * Creates a modified version of images
     * The media manager takes care of generating
     * those modified versions and putting them
     * in the right place. This is normally the
     * /media folder of your installation, but
     * could potentially also be a CDN or any other
     * place.
     *
     * @param array $options
     * @return self
     */
    public function thumb(array $options = []): self
    {
        if ($this->original() !== null) {
            $original = $this->original();
            $options  = array_merge($this->modifications(), $options);
        } else {
            $original = $this;
        }

        $kirby = $this->kirby();
        $url   = $kirby->component('file::url')($kirby, $this, $options);

        return $this->clone([
            'modifications' => $options,
            'original'      => $original,
            'url'           => $url,
        ]);
    }
}
