<?php

namespace Kirby\Cms;

use Kirby\Toolkit\View;
use Kirby\Toolkit\Url;

class Panel
{

    protected $kirby;

    public function __construct($kirby = null)
    {

        if (is_array($kirby) === true) {
            $this->kirby = new App($kirby);
        }

        if (is_a($this->kirby, App::class) === false) {
            $this->kirby = new App([
                'urls' => [
                    'index' => dirname(Url::index())
                ]
            ]);
        }

    }

    public function render(): string
    {
        return new View($this->kirby->root('kirby') . '/views/panel.php', [
            'kirby'   => $this->kirby,
            'plugins' => Resources::forPlugins()
        ]);
    }

}
