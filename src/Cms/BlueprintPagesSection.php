<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\A;

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

        if (is_string($template) === true) {
            $template = [$template];
        }

        if (is_array($template) !== true) {
            throw new Exception('Invalid child template');
        }

        // an array of template options
        $options = [];

        // convert every template to a usable option array
        // for the template select box
        foreach ($template as $templateName) {

            // create a dummy child page to load the blueprint
            $child = new Page([
                'slug'     => 'tmp',
                'template' => $templateName
            ]);

            $options[] = [
                'value' => $templateName,
                'text'  => $child->blueprint()->title()
            ];

        }

        return $options;

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

    protected function itemImageDefault($item)
    {
        return $item->image();
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
        if (in_array($data['template'], A::pluck($options['template'], 'value')) === false) {
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
                    return $this->section()->paginate($this->requestQuery('page', 1), $this->requestQuery('limit'))->toArray();
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
