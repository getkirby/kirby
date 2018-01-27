<?php

namespace Kirby\Cms;

class Snippet extends Template
{

    protected function missingViewMessage(): string
    {
        return sprintf('The snippet "%s" cannot be found', $this->name());
    }

    public function root(): string
    {
        return App::instance()->root('snippets');
    }

}
