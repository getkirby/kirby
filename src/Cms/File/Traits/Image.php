<?php

namespace Kirby\Cms\File\Traits;

use Closure;
use Exception;
use Kirby\Cms\App;
use Kirby\FileSystem\Folder;
use Kirby\Image\Darkroom;
use Kirby\Image\Image as Asset;

trait Image
{

    public function resize(int $width = null, int $height = null, int $quality = null): Asset
    {
        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality
        ]);
    }

    public function crop(int $width, int $height = null, int $quality = null): Asset
    {
        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality,
            'crop'    => 'center'
        ]);
    }

    public function thumb(array $options = []): Asset
    {

        // TODO: replace this with a loosely-coupled alternative
        // that can be replaced by a plugin
        $app = App::instance();

        if ($app === null) {
            throw new Exception('Missing app instance');
        }

        // get the darkroom instance
        $darkroom = $app->darkroom();

        if (is_a($darkroom, Darkroom::class) == false) {
            throw new Exception('Invalid Darkroom instance');
        }

        // preprocess the expected image attributes
        $attributes     = $darkroom->preprocess($this->root(), $options);
        $attributeChain = $this->attributeChain($attributes);

        // copy the original to a temporary location
        $thumbFilename = $this->name() . '-' . $attributeChain . '.' . $this->extension();
        $thumbDir      = $app->root('files') . '/' . $this->page()->id();
        $thumbUrl      = $app->url('files') . '/' . $this->page()->id() . '/' . $thumbFilename;
        $thumbRoot     = $thumbDir . '/' . $thumbFilename;
        $thumbFolder   = new Folder($thumbDir);
        $thumbObject   = new Asset($thumbRoot, $thumbUrl);

        // check if the thumbnail already exists and is not expired
        if ($thumbObject->exists() && $thumbObject->modified() >= $this->modified()) {
            return $thumbObject;
        }

        // create the folder
        $thumbFolder->make(true);

        // copy the original
        $this->asset->copy($thumbRoot, true);

        // create the processed image version from the temp file
        $result = $app->darkroom()->process($thumbRoot, $attributes);

        return $thumbObject;

    }

    protected function attributeChain(array $attributes)
    {

        $options = [
            'crop' => [
                'default' => 'center',
                'key'     => 'crop',
            ],
            'blur' => [
                'default' => false,
                'key'     => 'blur',
            ],
            'quality' => [
                'default' => 100,
                'key'     => 'q',
            ],
            'grayscale' => [
                'default' => false,
                'key' => 'bw'
            ]
        ];

        $chain = [];

        foreach ($attributes as $key => $value) {
            if ($value !== false && $value !== null && isset($options[$key]) && $options[$key]['default'] !== $value) {
                if ($value === true) {
                    $chain[] = $options[$key]['key'];
                } else {
                    $chain[] = $options[$key]['key'] . $value;
                }
            }
        }

        sort($chain);

        // add the dimensions to the chain
        array_unshift($chain, ($attributes['width'] ?? '') . 'x' . ($attributes['height'] ?? '' ));

        return implode('-', $chain);

    }

}
