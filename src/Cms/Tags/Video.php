<?php

namespace Kirby\Cms\Tags;

use Kirby\Cms\App;
use Kirby\Html\Element;
use Kirby\Html\Element\Video as Iframe;

class Video extends \Kirby\Text\Tags\Video
{
    use Dependencies;

    protected function iframe(): Element
    {
        $options = $this->kirby()->options();

        // url option queries
        $query = [
            'vimeo'   => $options['kirbytext.video.vimeo.options']   ?? [],
            'youtube' => $options['kirbytext.video.youtube.options'] ?? [],
        ];

        return Iframe::create($this->value(), $query, [
            'class'  => $options['kirbytext.video.class']  ?? 'video',
            'height' => $options['kirbytext.video.height'] ?? false,
            'width'  => $options['kirbytext.video.width']  ?? false,
        ]);
    }
}
