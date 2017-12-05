<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\Page;

class PagesSectionAdd
{

    protected $section;
    protected $options;

    public function __construct(PagesSection $section, $options)
    {

        // the section is needed to do useful object queries
        $this->section = $section;

        // convert simple template strings to a usable array
        if (is_string($options) === true) {
            // only one template definition
            $options = [
                'templates' => [$options]
            ];
        }

        // stop if at this point there's no usable definition array
        if (is_array($options) === false) {
            throw new Exception('Invalid definition of the "add" options');
        }

        // stop if there's no template definition
        if (empty($options['templates']) === true) {
            throw new Exception('Please define the templates for child pages');
        }

        // default options
        $defaults = [
            'parent' => 'self'
        ];

        $this->options = array_merge($defaults, $options);

    }

    public function parent()
    {
        // query the parent page
        $parent = $this->section->query($this->options['parent']);

        // only allow pages as parents
        if (is_a($parent, Page::class) === true) {
            return $parent->id();
        }

        return null;
    }

    public function templates(): array
    {
        if (is_array($this->options['templates']) === true) {
            return $this->options['templates'];
        }

        if (is_string($this->options['templates'])) {
            return [$this->options['templates']];
        }

        throw new Exception('Invalid templates definition for child pages');
    }

    public function toArray(): array
    {
        return [
            'parent'    => $this->parent(),
            'templates' => $this->templates()
        ];
    }

}
