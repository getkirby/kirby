<?php

namespace Kirby\Form\Mixins;

use Kirby\Util\I18n;

trait Help
{

    protected $help;

    protected function defaultHelp()
    {
        return null;
    }

    public function help()
    {
        return $this->help;
    }

    /**
     * Set the field help text
     *
     * @param string|array $help
     * @return self
     */
    protected function setHelp($help = null): self
    {
        $this->help = I18n::translate($help, $help);
        return $this;
    }

}
