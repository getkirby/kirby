<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\File;
use Kirby\Cms\Page;

class Add
{

    protected $section;
    protected $options;

    public function __construct(FilesSection $section, $options)
    {

        // the section is needed to do useful object queries
        $this->section = $section;

        // single accept setting
        if (is_string($options) === true) {
            $options = [
                'accept' => $options
            ];
        }

        // default options
        $defaults = [
            'parent'   => 'self',
            'accept'   => null,
            'multiple' => true,
            'meta'     => null
        ];

        $this->options = array_merge($defaults, $options);

    }

    public function parent()
    {
        // query the parent page
        $parent = $this->section->query($this->options['parent']);

        // only allow pages as parents
        if (is_a($parent, Page::class) === true) {
            return $parent;
        }

        return null;
    }

    public function url(): string
    {
        if ($parent = $this->parent()) {
            return kirby()->url('api') . '/pages/' . $this->parent()->id() . '/files';
        }

        return kirby()->url('api') . '/site/files';
    }

    public function multiple()
    {
        if ($this->section->max() === 1) {
            return false;
        }

        if ($this->section->max() - $this->section->total() === 1) {
            return false;
        }

        return $this->options['multiple'];
    }

    public function meta()
    {
        $meta = $this->options['meta'];

        // automatically add the group to the meta file
        if ($group = $this->section->group()) {
            $meta['group'] = $group;
        }

        return $meta;
    }

    public function toArray(): array
    {
        return [
            'url'        => $this->url(),
            'accept'     => $this->options['accept'],
            'multiple'   => $this->multiple(),
            'attributes' => $this->meta()
        ];
    }

}
