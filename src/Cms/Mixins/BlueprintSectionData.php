<?php

namespace Kirby\Cms\Mixins;

use Exception;
use Kirby\Cms\File;

trait BlueprintSectionData
{

    use BlueprintSectionMax;
    use BlueprintSectionMin;

    protected $data;
    protected $error;
    protected $item;
    protected $query;
    protected $total;

    public function data()
    {
        if (is_a($this->data, static::ACCEPT) === true) {
            return $this->data;
        }

        $data = $this->stringQuery($this->query());

        if (is_a($data, static::ACCEPT) === false) {
            throw new Exception('Invalid data type');
        }

        return $this->data = $data;
    }

    protected function defaultQuery(): string
    {
        return 'site.children';
    }

    public function error()
    {
        try {
            $this->validate();
            return null;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function item(): array
    {
        return $this->item ?? [];
    }

    protected function itemImageResult($item, $stringTemplateData)
    {
        $image = $this->itemValue($item, 'image', $stringTemplateData);

        if ($image !== null && is_a($image, File::class) === true && $image->type() === 'image') {
            return $image->url();
        }

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
            'title' => $this->itemValue($item, 'title', $stringTemplateData),
            'image' => $this->itemImageResult($item, $stringTemplateData),
            'link'  => $this->itemLink($item),
            'info'  => $this->itemValue($item, 'info', $stringTemplateData),
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

    protected function setQuery(string $query)
    {
        $this->query = $query;
        return $this;
    }

    public function total(): int
    {
        if ($pagination = $this->data()->pagination()) {
            return $pagination->total();
        }

        return $this->data()->count();
    }

    protected function validate(): bool
    {
        $this->validateMin();
        $this->validateMax();

        return true;
    }

}
