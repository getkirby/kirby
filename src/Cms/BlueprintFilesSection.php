<?php

namespace Kirby\Cms;

use Kirby\Http\Acceptance\MimeType;
use Kirby\Image\Image;
use Kirby\Toolkit\V;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;

class BlueprintFilesSection extends BlueprintSection
{
    const ACCEPT = Files::class;

    use Mixins\BlueprintSectionHeadline;
    use Mixins\BlueprintSectionLayout;
    use Mixins\BlueprintSectionData;

    protected $add;
    protected $accept;
    protected $template;

    public function accept()
    {
        if ($template = $this->template()) {
            $file = new File([
                'filename' => 'tmp',
                'template' => $template
            ]);

            return $file->blueprint()->accept()['mime'] ?? '*';
        }

        return null;
    }

    public function add(): bool
    {
        if ($this->isFull() === true) {
            return false;
        }
        return true;
    }

    /**
     * Fetch data for the applied settings
     *
     * @return Files
     */
    public function data(): Files
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $parent = $this->parent();

        if ($parent === null) {
            throw new LogicException('The parent page cannot be found');
        }

        $data = $parent->files();

        // filter by the template
        if ($template = $this->template()) {
            $data = $data->filterBy('template', $template);
        }

        if ($this->sortBy() && $this->sortable() === false) {
            $data = $data->sortBy(...Str::split($this->sortBy(), ' '));
        } elseif ($this->sortable() === true) {
            $data = $data->sortBy('sort', 'asc');
        }

        // store the original data to reapply pagination later
        $this->originalData = $data;

        // apply the default pagination
        return $this->data = $data->paginate([
            'page'  => 1,
            'limit' => $this->limit()
        ]);
    }

    protected function defaultSortable(): bool
    {
        return true;
    }

    public function filename($source, $filename, $template = null)
    {
        $extension = F::extension($filename);
        $name      = F::name($filename);

        if (empty($template) === false) {
            $image = new Image($source);
            $data  = [
                'file' => [
                    'height'      => $image->height(),
                    'name'        => F::name($filename),
                    'orientation' => $image->orientation(),
                    'type'        => $image->type(),
                    'width'       => $image->width(),
                ],
                'index' => $this->total() + 1
            ];

            $name = Str::template($template, $data);
        }

        $name = Str::slug($name);

        return $name . '.' . $extension;
    }

    protected function itemTitle($item)
    {
        return $item->filename();
    }

    protected function itemInfo($item)
    {
        return null;
    }

    protected function itemImageDefault($item)
    {
        return $item;
    }

    protected function itemLink($item)
    {
        if (is_a($item->parent(), Page::class) === true) {
            return '/pages/' . str_replace('/', '+', $item->parent()->id()) . '/files/' . $item->filename();
        }

        return '/site/files/' . $item->filename();
    }

    protected function itemToResult($item)
    {
        $stringTemplateData = [$this->modelType($item) => $item];

        if (is_a($item->parent(), Page::class) === true) {
            $parent = $item->parent()->id();
        } else {
            $parent = null;
        }

        return [
            'filename' => $item->filename(),
            'id'       => $item->id(),
            'parent'   => $parent,
            'text'     => $this->itemValue($item, 'title', $stringTemplateData),
            'image'    => $this->itemImage($item, $stringTemplateData),
            'link'     => $this->itemLink($item),
            'info'     => $this->itemValue($item, 'info', $stringTemplateData),
            'url'      => $item->url()
        ];
    }

    public function upload(array $data)
    {
        // make sure the basics are provided
        if (isset($data['filename'], $data['source']) === false) {
            throw new InvalidArgumentException([
                'key' => 'file.name.missing'
            ]);
        }

        // check if adding files is allowed at all
        if ($this->add() === false) {
            throw new LogicException([
                'key' => 'blueprint.section.files.add'
            ]);
        }

        $file = $this->parent()->createFile([
            'source'   => $data['source'],
            'template' => $this->template(),
            'filename' => $this->filename($data['source'], $data['filename'])
        ]);

        return $file;
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
                    return $this->upload(function ($source, $filename) {
                        return $this->section()->upload([
                            'content'  => $this->requestBody('content'),
                            'filename' => $filename,
                            'source'   => $source,
                        ]);
                    });
                }
            ],
            'sort' => [
                'pattern' => 'sort',
                'method'  => 'PATCH',
                'action'  => function () {
                    return $this->section()->sort($this->requestBody('items'));
                }
            ]
        ];
    }

    protected function setTemplate(string $template = null)
    {
        $this->template = $template;
        return $this;
    }

    public function sort(array $input)
    {
        if ($this->sortable() === false) {
            throw new LogicException([
                'key' => 'blueprint.section.files.sort'
            ]);
        }

        $files = $this->parent()->files();

        foreach ($input as $index => $id) {
            $file = $files->findBy('id', $id);
            $file->changeSort($index + 1);
        }

        return true;
    }

    public function template()
    {
        return $this->template;
    }
}
