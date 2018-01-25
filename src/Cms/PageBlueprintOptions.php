<?php

namespace Kirby\Cms;

class PageBlueprintOptions extends BlueprintObject
{

    protected static $toArray = [
        'delete',
        'template',
        'url'
    ];

    protected $delete;
    protected $template;
    protected $url;

    /**
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $this->setOptionalProperties($props, ['delete', 'template', 'url']);
    }

    /**
     * @return boolean
     */
    public function delete(): bool
    {
        return $this->delete ?? true;
    }

    /**
     * @return boolean
     */
    public function template(): bool
    {
        return $this->template ?? true;
    }

    /**
     * @return boolean
     */
    public function url(): bool
    {
        return $this->url ?? true;
    }

    /**
     * @param boolean $delete
     * @return self
     */
    protected function setDelete(bool $delete = true): self
    {
        $this->delete = $delete;
        return $this;
    }

    /**
     * @param boolean $template
     * @return self
     */
    protected function setTemplate(bool $template = true): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @param boolean $url
     * @return self
     */
    protected function setUrl(bool $url = true): self
    {
        $this->url = $url;
        return $this;
    }

}
