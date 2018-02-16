<?php

namespace Kirby\Cms;

use Exception;

class BlueprintPagesSection extends BlueprintSection
{

    const ACCEPT = Pages::class;

    use Mixins\BlueprintSectionHeadline;
    use Mixins\BlueprintSectionLayout;
    use Mixins\BlueprintSectionData;

    protected $create = false;

    protected function defaultQuery(): string
    {
        return 'page.children';
    }

    protected function childContent(): array
    {
        return empty($this->create['content']) ? [] : $this->create['content'];
    }

    protected function childTemplates(): array
    {
        $template = $this->create['template'] ?? null;

        if (empty($template) === true) {
            $template = $this->data()->first()->template() ?? 'default';
        }

        if (is_array($template) === true) {
            return $template;
        }

        if (is_string($template) === true) {
            return [$template];
        }

        throw new Exception('Invalid child template');
    }

    public function create()
    {
        if (empty($this->create) === true) {
            return false;
        }

        if ($this->isFull() === true) {
            return false;
        }

        $result = [
            'content'  => $this->childContent(),
            'template' => $this->childTemplates(),
        ];

        return $result;
    }

    protected function itemImage($item, array $data)
    {
        $query = $this->item()['image'] ?? null;

        if ($query === null) {
            return $item->image();
        }

        if ($query !== false) {
            return (new Query($this->item()['image'], $data))->result();
        }

        return null;
    }

    protected function itemLink($item)
    {
        return '/pages/' . str_replace('/', '+', $item->id());
    }

    public function post(array $data)
    {
        // make sure the basics are provided
        if (isset($data['slug'], $data['template']) === false) {
            throw new Exception('Please provide a slug and template');
        }

        // get all create options from the blueprint
        $options = $this->create();

        // check if adding subpages is allowed at all
        if (empty($options)) {
            throw new Exception('No subpages can be added');
        }

        // make sure we don't allow more entries than accepted
        if ($this->isFull()) {
            throw new Exception('Too many entries');
        }

        // merge the post data with the pre-defined content set in the blueprint
        $content = array_merge($data['content'] ?? [], $options['content']);

        // validate the template
        if (in_array($data['template'], $options['template']) === false) {
            throw new Exception('Invalid template');
        }

        return $this->parent()->createChild([
            'content'  => $content,
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
                    return $this->section()->paginate($this->requestQuery('page', 1), $this->requestQuery('limit', 20))->toArray();
                }
            ],
            'create' => [
                'pattern' => '/',
                'method'  => 'POST',
                'action'  => function () {
                    return $this->section()->post($this->requestBody());
                }
            ]
        ];
    }

    protected function setCreate($create = false)
    {
        if (is_string($create) === true) {
            $create = [
                'template' => [$create]
            ];
        }

        if (is_array($create) === false && $create !== false) {
            throw new Exception('Invalid child creation setup');
        }

        $this->create = $create;
        return $this;
    }

}
