<?php

namespace Kirby\Cms\Tags;

use Kirby\Cms\App;
use Kirby\Html\Element;
use Kirby\Html\Element\Video as Iframe;

class Video extends \Kirby\Text\Tags\Video
{
    use Dependencies;

    protected $options;
    protected $attr;

    public function __construct()
    {
        $config = $this->kirby()->options();

        $this->attr = [
            'class'  => $config['kirbytext.video.class']  ?? 'video',
            'height' => $config['kirbytext.video.height'] ?? false,
            'width'  => $config['kirbytext.video.width']  ?? false,
        ];

        $this->options = [
            'vimeo'   => $config['kirbytext.video.vimeo.options']   ?? [],
            'youtube' => $config['kirbytext.video.youtube.options'] ?? [],
        ];
    }

    protected function iframe(): Element
    {
        return Iframe::create($this->value(), $this->options, $this->attr);
    }
}
