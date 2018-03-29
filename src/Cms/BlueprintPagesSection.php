<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\A;
use Kirby\Util\Str;

class BlueprintPagesSection extends BlueprintSection
{

    const ACCEPT = Pages::class;

    use Mixins\BlueprintSectionHeadline;
    use Mixins\BlueprintSectionLayout;
    use Mixins\BlueprintSectionData;

    protected $add;
    protected $blueprints;
    protected $parent;
    protected $sortable;
    protected $sortBy;
    protected $status;
    protected $group;
    protected $templates;

    public function add(): bool
    {
        if ($this->status() !== 'draft') {
            return false;
        }

        if ($this->isFull() === true) {
            return false;
        }

        return count($this->blueprints()) > 0;
    }

    public function blueprints(): array
    {
        $blueprints = [];

        // convert every template to a usable option array
        // for the template select box
        foreach ($this->templates() as $template) {

            // create a dummy child page to load the blueprint
            $child = new Page([
                'slug'     => 'tmp',
                'template' => $template
            ]);

            $blueprint = $child->blueprint();

            $blueprints[] = [
                'name'  => $blueprint->name(),
                'title' => $blueprint->title(),
                'num'   => $blueprint->num(),
            ];
        }

        return $blueprints;
    }

    /**
     * Fetch data for the applied settings
     *
     * @return Pages
     */
    public function data(): Pages
    {
        if ($this->data !== null) {
            return $this->data;
        }

        switch ($this->status()) {
            case 'draft':
                $data = $this->parent()->drafts();
                break;
            case 'listed':
                $data = $this->parent()->children()->listed();
                break;
            case 'published':
                $data = $this->parent()->children();
                break;
            case 'unlisted':
                $data = $this->parent()->children()->unlisted();
                break;
        }

        // filter by all set templates
        if ($templates = $this->templates()) {
            $data = $data->filterBy('template', 'in', $templates);
        }

        if ($this->sortBy() && $this->sortable() === false) {
            $data = $data->sortBy(...Str::split($this->sortBy(), ' '));
        }

        // store the original data to reapply pagination later
        $this->originalData = $data;

        // apply the default pagination
        return $this->data = $data->paginate([
            'page'  => 1,
            'limit' => $this->limit()
        ]);
    }

    protected function defaultStatus(): string
    {
        return 'published';
    }

    public function group(): string
    {
        $parent = $this->parent();

        if (is_a($parent, Site::class) === true) {
            return 'site';
        } else {
            return $parent->id();
        }
    }

    protected function itemImageDefault($item)
    {
        return $item->image();
    }

    protected function itemLink($item)
    {
        return '/pages/' . str_replace('/', '+', $item->id());
    }

    /**
     * Resolve the given parent setting to
     * a page model
     *
     * @return Page|Site|null
     */
    public function parent()
    {
        if (is_string($this->parent) === true) {
            return $this->parent = $this->stringQuery($this->parent);
        }

        if ($this->parent === null) {
            return $this->parent = $this->model();
        }

        return $this->parent;
    }

    public function post(array $data)
    {
        if ($this->add() === false) {
            throw new Exception('You cannot add any pages to the section');
        }

        // make sure the basics are provided
        if (isset($data['slug'], $data['template']) === false) {
            throw new Exception('Please provide a slug and template');
        }

        // validate the template
        if (in_array($data['template'], $this->templates()) === false) {
            throw new Exception('Invalid template');
        }

        return $this->parent()->createChild([
            'content'  => $data['content'],
            'slug'     => $data['slug'],
            'template' => $data['template']
        ]);
    }

    public function routes(): array
    {
        return [
            'read'   => [
                'pattern' => '/',
                'method'  => 'GET',
                'action'  => function () {
                    return $this->section()->paginate($this->requestQuery('page', 1), $this->requestQuery('limit'))->toArray();
                }
            ],
            'create' => [
                'pattern' => '/',
                'method'  => 'POST',
                'action'  => function () {
                    return $this->section()->post($this->requestBody());
                }
            ],
            'sort' => [
                'pattern' => 'sort',
                'method'  => 'PATCH',
                'action'  => function () {
                    return $this->section()->sort($this->requestBody('page'), $this->requestBody('status'), $this->requestBody('position'));
                }
            ]
        ];
    }

    protected function setParent(string $parent = null): self
    {
        $this->parent = $parent;
        return $this;
    }

    protected function setSortBy(string $sortBy = null): self
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    protected function setStatus(string $status): self
    {
        if ($status === 'drafts') {
            $status = 'draft';
        }

        if (in_array($status, ['draft', 'published', 'listed', 'unlisted']) === false) {
            throw new Exception('Invalid status: ' . $status);
        }

        $this->status = $status;
        return $this;
    }

    /**
     * Allowed templates can be defined as a single string
     * or array. Setting a template will automatically create
     * a filter on the data query and also set the allowed
     * templates when creating new subpages.
     *
     * @param string|array $template
     * @return self
     */
    protected function setTemplates($templates = null): self
    {
        if (is_string($templates) === true) {
            $templates = [$templates];
        }

        if ($templates === null) {
            $templates = [];
        }

        if (is_array($templates) === false) {
            throw new Exception('Invalid template definition');
        }

        $this->templates = $templates;
        return $this;
    }

    public function sort(string $id, string $status, int $position = null)
    {
        if ($this->status() === 'published') {
            throw new Exception('This section has listed and unlisted pages. Pages must be sorted manually.');
        }

        $page = $this->parent()->children()->findBy('id', $id);

        if ($page === null) {
            $page = $this->parent()->drafts()->findBy('id', $id);
        }

        if (is_a($page, Page::class) === false) {
            throw new Exception('The page cannot be dragged into this section');
        }

        if (empty($this->templates()) === false && in_array($page->template(), $this->templates()) === false) {
            throw new Exception('The page template is not allowed in this section');
        }

        return $page->changeStatus($status, $position);
    }

    public function sortable(): bool
    {
        if ($this->status() !== 'listed') {
            return false;
        }

        foreach ($this->blueprints() as $blueprint) {
            if ($blueprint['num'] !== 'default') {
                return false;
            }
        }

        return true;
    }

    public function sortBy()
    {
        return $this->sortBy;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function templates()
    {
        return $this->templates;
    }

}
