<?php

namespace Kirby\Cms;

class Tempura
{

    protected $template;
    protected $data;

    public function __construct(string $template, array $data = [])
    {
        $this->template = $template;
        $this->data     = $data;
    }

    public function render(): string
    {
        return preg_replace_callback('!{{(.*?)}}!', function ($match) {
            return (new Query($match[1], $this->data))->result();
        }, $this->template);
    }

    public function __toString(): string
    {
        return $this->render();
    }

}
