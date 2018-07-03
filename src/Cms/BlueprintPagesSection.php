<?php

namespace Kirby\Cms;

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;

class BlueprintPagesSection extends BlueprintSection
{
    const ACCEPT = Pages::class;

    use Mixins\BlueprintSectionHeadline;
    use Mixins\BlueprintSectionLayout;
    use Mixins\BlueprintSectionData;

    protected $add;
    protected $blueprints;
    protected $sortable;
    protected $status;
    protected $templates;

    public function add(): bool
    {
        if (in_array($this->status(), ['draft', 'all']) === false) {
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

        $parent = $this->parent();

        if ($parent === null) {
            throw new LogicException('The parent page cannot be found');
        }

        switch ($this->status()) {
            case 'draft':
                $data = $parent->drafts();
                break;
            case 'listed':
                $data = $parent->children()->listed();
                break;
            case 'published':
                $data = $parent->children();
                break;
            case 'unlisted':
                $data = $parent->children()->unlisted();
                break;
            default:
                $data = $parent->children()->merge('drafts');
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
        return 'all';
    }


    protected function itemImageDefault($item)
    {
        return $item->image();
    }

    protected function itemLink($item)
    {
        return '/pages/' . str_replace('/', '+', $item->id());
    }

    protected function itemToResult($item)
    {
        $stringTemplateData = [$this->modelType($item) => $item];

        return [
            'icon'             => $this->itemIcon($item),
            'id'               => $item->id(),
            'image'            => $this->itemImage($item, $stringTemplateData),
            'info'             => $this->itemValue($item, 'info', $stringTemplateData),
            'link'             => $this->itemLink($item),
            'parent'           => $item->parent() ? $item->parent()->id(): null,
            'status'           => $item->status(),
            'text'             => $this->itemValue($item, 'title', $stringTemplateData),
            'url'              => $item->url(),
            'permissions'      => [
                'sort'         => $item->isSortable(),
                'changeStatus' => $item->permissions()->changeStatus(),
            ],
        ];
    }

    public function post(array $data)
    {
        if ($this->add() === false) {
            throw new LogicException([
                'key' => 'blueprint.section.pages.add'
            ]);
        }

        // make sure the slug is provided
        if (isset($data['slug']) === false) {
            throw new InvalidArgumentException([
                'key' => 'page.slug.invalid'
            ]);
        }

        // make sure the template is provided
        if (isset($data['slug']) === false) {
            throw new InvalidArgumentException([
                'key' => 'page.template.missing'
            ]);
        }

        // validate the template
        if (in_array($data['template'], $this->templates()) === false) {
            throw new InvalidArgumentException([
                'key' => 'page.template.invalid'
            ]);
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

    protected function setStatus(string $status): self
    {
        if ($status === 'drafts') {
            $status = 'draft';
        }

        if (in_array($status, ['all', 'draft', 'published', 'listed', 'unlisted']) === false) {
            throw new InvalidArgumentException([
                'key' => 'page.status.invalid'
            ]);
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
            throw new InvalidArgumentException('Invalid template definition');
        }

        $this->templates = $templates;
        return $this;
    }

    public function sort(string $id, string $status, int $position = null)
    {
        if (in_array($this->status(), ['all', 'published']) === true) {
            $status = 'listed';
        }

        $page = $this->parent()->children()->findBy('id', $id);

        if ($page === null) {
            $page = $this->parent()->drafts()->findBy('id', $id);
        }

        if (is_a($page, Page::class) === false) {
            throw new LogicException([
                'key' => 'page.sort.section.type'
            ]);
        }

        if (empty($this->templates()) === false && in_array($page->template(), $this->templates()) === false) {
            throw new LogicException([
                'key' => 'page.sort.section.template.invalid'
            ]);
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

    public function status(): string
    {
        return $this->status;
    }

    public function templates()
    {
        return $this->templates;
    }
}
