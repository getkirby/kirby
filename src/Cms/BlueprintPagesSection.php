<?php

namespace Kirby\Cms;

use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
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
    protected $dragTextType;
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
        if ($this->blueprints !== null) {
            return $this->blueprints;
        }

        $blueprints = [];
        $templates  = $this->templates();

        if (empty($templates) === true) {
            foreach (glob(App::instance()->root('blueprints') . '/pages/*.yml') as $blueprint) {
                $templates[] = F::name($blueprint);
            }
        }

        // convert every template to a usable option array
        // for the template select box
        foreach ($templates as $template) {

            // create a dummy child page to load the blueprint
            $child = new Page([
                'slug'     => 'tmp',
                'template' => $template
            ]);

            $blueprint = $child->blueprint();

            $blueprints[] = [
                'name'  => $blueprint->name(),
                'title' => $blueprint->title(),
            ];
        }

        return $this->blueprints = $blueprints;
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
            $data = $data->template($templates);
        }

        if ($this->sortBy()) {
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

    protected function dragTextType()
    {
        if ($this->dragTextType !== null) {
            return $this->dragTextType;
        }

        return $this->dragTextType = (App::instance()->option('panel')['kirbytext'] ?? true) ? 'kirbytext' : 'markdown';
    }

    protected function itemImageDefault($item)
    {
        return $item->image();
    }

    protected function itemToResult($item)
    {
        $stringTemplateData = [$this->modelType($item) => $item];

        return [
            'dragText'         => $item->dragText($this->dragTextType()),
            'icon'             => $this->itemIcon($item),
            'id'               => $item->id(),
            'image'            => $this->itemImage($item, $stringTemplateData),
            'info'             => $item->toString($this->item['info'] ?? ''),
            'link'             => '/pages/' . $item->panelId(),
            'parent'           => $item->parent() ? $item->parent()->id(): null,
            'status'           => $item->status(),
            'text'             => $item->toString($this->item['title'] ?? '{{ page.title }}'),
            'url'              => $item->url(),
            'permissions'      => [
                'sort'         => $item->isSortable(),
                'changeStatus' => $item->permissions()->changeStatus(),
            ],
        ];
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

    public function sortable(): bool
    {
        if ($this->status() !== 'listed' && $this->status() !== 'all') {
            return false;
        }

        if ($this->sortBy() !== null) {
            return false;
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
