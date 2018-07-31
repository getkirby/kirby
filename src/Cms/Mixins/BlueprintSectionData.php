<?php

namespace Kirby\Cms\Mixins;

use Exception;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Query;
use Kirby\Toolkit\Str;

trait BlueprintSectionData
{
    use BlueprintSectionMax;
    use BlueprintSectionMin;

    protected $data;
    protected $errors;
    protected $item;
    protected $limit = 20;
    protected $link;
    protected $originalData;
    protected $parent;
    protected $sortable;
    protected $sortBy;
    protected $pagination;

    protected function convertDataToArray(): array
    {
        return $this->result();
    }

    protected function convertPaginationToArray(): array
    {
        $pagination = $this->pagination();

        return [
            'limit'  => $pagination->limit(),
            'offset' => $pagination->offset(),
            'page'   => $pagination->page(),
            'total'  => $pagination->total(),
        ];
    }

    protected function convertParentToArray()
    {
        $parent = $this->parent();

        if (is_a($parent, 'Kirby\Cms\Page') === true) {
            return $parent->id();
        }

        return null;
    }

    public function data()
    {
        throw new Exception('Undefined data handler');
    }

    protected function defaultQuery(): string
    {
        return 'site.children';
    }

    protected function defaultSortable(): bool
    {
        return false;
    }

    public function errors(): array
    {
        try {
            $this->validate();
            return [];
        } catch (Exception $e) {
            return [
                [
                    'name'    => $this->name(),
                    'label'   => $this->headline(),
                    'message' => $e->getMessage(),
                    'type'    => 'section',
                ]
            ];
        }
    }

    public function isFull(): bool
    {
        if ($max = $this->max()) {
            return $this->total() >= $this->max();
        }

        return false;
    }

    public function item(): array
    {
        return $this->item ?? [];
    }

    protected function itemIcon($item)
    {
        if ($icon = $item->blueprint()->icon()) {
            if (strlen($icon) !== Str::length($icon)) {
                return [
                    'type'  => $icon,
                    'back'  => 'black',
                    'emoji' => true
                ];
            }

            return [
                'type' => $icon,
                'back' => 'black',
            ];
        }

        return [
            'type' => 'file',
            'back' => 'black',
        ];
    }

    protected function itemImage($item, array $data)
    {
        $imageSettings = $this->item()['image'] ?? null;
        $imageSource   = null;

        if (is_array($imageSettings) === false) {
            $imageSource   = $imageSettings;
            $imageSettings = [];
        } else {
            $imageSource = $imageSettings['src'] ?? null;
        }

        // remove the src from the settings
        unset($imageSettings['src']);

        // add defaults
        $imageSettings = array_merge([
            'url'   => false,
            'ratio' => '3/2',
            'back'  => 'pattern'
        ], $imageSettings);

        if ($imageSource === null) {
            $imageSource = $this->itemImageDefault($item);
        }

        if (is_string($imageSource) === true) {
            $imageSource = (new Query($imageSource, $data))->result();
        }

        if (is_a($imageSource, 'Kirby\Cms\File') === true && $imageSource->type() === 'image') {
            $url  = $this->layout() === 'list' ? $imageSource->crop(100)->url() : $imageSource->resize(400, 400)->url();
            $url .= '?t=' . $imageSource->modified();

            $imageSettings['url'] = $url;
        } else {
            $imageSettings['url'] = false;
        }

        return $imageSettings;
    }

    protected function itemImageDefault($item)
    {
        return null;
    }

    protected function itemLink($item)
    {
        return $item->id();
    }

    protected function itemTitle($item)
    {
        return $item->title()->value();
    }

    protected function itemToResult($item)
    {
        $stringTemplateData = [$this->modelType($item) => $item];

        return [
            'id'     => $item->id(),
            'parent' => $item->parent() ? $item->parent()->id() : null,
            'text'   => $this->itemValue($item, 'title', $stringTemplateData),
            'image'  => $this->itemImage($item, $stringTemplateData),
            'icon'   => $this->itemIcon($item),
            'link'   => $this->itemLink($item),
            'info'   => $this->itemValue($item, 'info', $stringTemplateData),
            'url'    => $item->url()
        ];
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function link()
    {
        $parent = $this->parent();
        $model  = $this->model();

        if ($parent === null) {
            return $this->linkForModel($model);
        }

        $parentClass = get_class($parent);
        $modelClass  = get_class($model);

        if ($parentClass !== $modelClass) {
            return $this->linkForModel($parent);
        }

        if ($parentClass === 'Kirby\Cms\Page' && $parent->is($model) === false) {
            return $this->linkForModel($parent);
        }

        return null;
    }

    protected function linkForModel($model)
    {
        if (is_a($model, 'Kirby\Cms\Page') === true) {
            return '/pages/' . str_replace('/', '+', $model->id());
        }

        if (is_a($model, 'Kirby\Cms\Site') === true) {
            return '/pages';
        }

        if (is_a($model, 'Kirby\Cms\User') === true) {
            return '/users/' . $model->id();
        }
    }

    public function originalData()
    {
        if ($this->originalData !== null) {
            return $this->originalData;
        }

        // query the data first
        $this->data();

        return $this->originalData;
    }

    public function paginate(int $page = 1, int $limit = null)
    {
        // overwrite the default pagination by using the original data set
        $this->data = $this->originalData()->paginate([
            'page'  => $page,
            'limit' => $limit ?? $this->limit()
        ]);

        return $this;
    }

    public function pagination()
    {
        return $this->data()->pagination();
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
            $this->parent = $this->stringQuery($this->parent);
        }

        if ($this->parent === null) {
            return $this->parent = $this->model();
        }

        return $this->parent;
    }

    public function result(): array
    {
        $result = [];

        foreach ($this->data() as $item) {
            $result[] = $this->itemToResult($item);
        }

        return $result;
    }

    protected function setItem(array $item = null)
    {
        $this->item = $item;
        return $this;
    }

    protected function setLimit(int $limit = null)
    {
        $this->limit = $limit;
        return $this;
    }

    protected function setParent(string $parent = null): self
    {
        $this->parent = $parent;
        return $this;
    }

    protected function setSortable(bool $sortable)
    {
        $this->sortable = $sortable;
        return $this;
    }

    protected function setSortBy(string $sortBy = null): self
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    public function sortable(): bool
    {
        return $this->sortable && !$this->sortBy;
    }

    public function sortBy()
    {
        return $this->sortBy;
    }

    public function total(): int
    {
        return $this->pagination()->total();
    }

    protected function validate(): bool
    {
        $this->validateMin();
        $this->validateMax();

        return true;
    }
}
