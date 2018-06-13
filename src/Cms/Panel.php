<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;
use Kirby\Toolkit\View;

class Panel
{

    protected $kirby;

    public function __construct(App $kirby)
    {
        $this->kirby = $kirby;
    }

    public function render(): string
    {
        return new View(__DIR__ . '/../../views/panel.php', [
            'kirby'     => $this->kirby,
            'assetUrl'  => $this->kirby->url('media') . '/panel',
            'pluginCss' => $this->kirby->url('media') . '/plugins/index.css',
            'pluginJs'  => $this->kirby->url('media') . '/plugins/index.js',
            'icons'     => F::read($this->kirby->root('media') . '/panel/img/icons.svg'),
            'panelUrl'  => $this->kirby->url('panel'),
            'options'   => [
                'url'         => $this->kirby->url('panel'),
                'site'        => $this->kirby->url('index'),
                'api'         => $this->kirby->url('api'),
                'translation' => 'en',
                'debug'       => true
            ]
        ]);
    }

}
