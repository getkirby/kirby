<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\F;

class Snippet extends Template
{

    protected function missingViewMessage(): string
    {
        return sprintf('The snippet "%s" cannot be found', $this->name());
    }

    public function file()
    {
        try {
            return F::realpath($this->root() . '/' . $this->name() . '.' . $this->extension(), $this->root());
        } catch (Exception $e) {
            // try to load the template from the registry
            return App::instance()->get('snippet', $this->name());
        }
    }

    public function root(): string
    {
        return App::instance()->root('snippets');
    }

}
