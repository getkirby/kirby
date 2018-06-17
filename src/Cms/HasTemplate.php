<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

trait HasTemplate
{

    /**
     * @var string
     */
    protected $template;

    /**
     * @param string $template
     * @return self
     */
    protected function setTemplate(string $template = null): self
    {
        $this->template = $template !== null ? Str::slug($template) : null;
        return $this;
    }

    /**
     * Returns the final template
     *
     * @return string|null
     */
    public function template()
    {
        return $this->template = $this->template ?? $this->store()->template();
    }
}
