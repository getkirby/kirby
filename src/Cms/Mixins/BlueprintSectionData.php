<?php

namespace Kirby\Cms\Mixins;

use Exception;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Cms\Query;

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
    protected $query;
    protected $pagination;

    protected function convertDataToArray(): array
    {
        return $this->result();
    }

    protected function convertPaginationToArray(): array
    {
        $pagination = $this->pagination();

        return [
            'limit' => $pagination->limit(),
            'page'  => $pagination->page(),
            'total' => $pagination->total(),
        ];
    }

    protected function convertParentToArray()
    {
        $parent = $this->parent();

        if (is_a($parent, Page::class) === true) {
            return $parent->id();
        }

        return null;
    }

    public function data()
    {
        if (is_a($this->data, static::ACCEPT) === true) {
            return $this->data;
        }

        $data = $this->stringQuery($this->query());

        if (is_a($data, static::ACCEPT) === false) {
            throw new Exception('Invalid data type');
        }

        $this->originalData = $data;

        // apply the default pagination
        return $this->data = $data->paginate([
            'page'  => 1,
            'limit' => $this->limit()
        ]);

    }

    protected function defaultQuery(): string
    {
        return 'site.children';
    }

    public function errors(): array
    {

        try {
            $this->validate();
            return [];
        } catch (Exception $e) {
            return [
                [
                    'type'    => 'exception',
                    'message' => $e->getMessage()
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

        if (is_a($imageSource, File::class) === true && $imageSource->type() === 'image') {
            $imageSettings['url'] = $this->layout() === 'list' ? $imageSource->crop(100)->url() : $imageSource->resize(300, 300)->url();
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
            'link'   => $this->itemLink($item),
            'info'   => $this->itemValue($item, 'info', $stringTemplateData),
            'url'    => $item->url()
        ];
    }

    protected function itemValue($item, string $key, array $data)
    {
        if ($value = $this->item[$key] ?? null) {
            return $this->stringTemplate($value, $data);
        }

        if (method_exists($this, 'item' . $key) === true) {
            return $this->{'item' . $key}($item);
        }

        return null;
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

        return null;
    }

    protected function linkForModel($model)
    {
        if (is_a($model, Page::class) === true) {
            return '/pages/' . str_replace('/', '+', $model->id());
        }

        if (is_a($model, Site::class) === true) {
            return '/pages';
        }

        if (is_a($model, User::class) === true) {
            return '/users/' . $model->id();
        }
    }

    public function originalData()
    {
        if (is_a($this->originalData, static::ACCEPT) === true) {
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

    public function parent()
    {
        if ($parent = $this->data()->parent()) {
            return $parent;
        }

        return $this->model();
    }

    public function query(): string
    {
        return $this->query;
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

    protected function setQuery(string $query)
    {
        $this->query = $query;
        return $this;
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
