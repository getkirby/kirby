<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Http\Acceptance\MimeType;
use Kirby\Image\Image;
use Kirby\Toolkit\V;
use Kirby\Util\F;
use Kirby\Util\Str;

class BlueprintFilesSection extends BlueprintSection
{

    const ACCEPT = Files::class;

    use Mixins\BlueprintSectionHeadline;
    use Mixins\BlueprintSectionLayout;
    use Mixins\BlueprintSectionData;

    protected $accept;
    protected $create;

    public function accept()
    {
        // accept anything
        if (empty($this->accept) === true) {
            return true;
        }

        $defaults = [
            'mime'        => null,
            'maxHeight'   => null,
            'maxSize'     => null,
            'maxWidth'    => null,
            'minHeight'   => null,
            'minSize'     => null,
            'minWidth'    => null,
            'orientation' => null
        ];

        return array_merge($defaults, $this->accept);

    }

    public function accepts($source): bool
    {
        $rules = $this->accept();

        if ($rules === true) {
            return true;
        }

        $image = new Image($source);

        if ($rules['mime'] !== null) {
            if ((new MimeType($rules['mime']))->has($image->mime()) === false) {
                throw new Exception('Invalid mime type');
            }
        }

        $validations = [
            'maxSize'     => ['size',   'max', 'The file is too large'],
            'minSize'     => ['size',   'min', 'The file is too small'],
            'maxWidth'    => ['width',  'max', 'The width of the image is too large'],
            'minWidth'    => ['width',  'min', 'The width of the image is too small'],
            'maxHeight'   => ['height', 'max', 'The height of the image is too large'],
            'minHeight'   => ['height', 'min', 'The height of the image is too small'],
            'orientation' => ['orientation', 'same', 'The orientation of the image is incorrect']
        ];

        foreach ($validations as $key => $arguments) {
            if ($rules[$key] !== null) {
                $property  = $arguments[0];
                $validator = $arguments[1];
                $message   = $arguments[2];

                if (V::$validator($image->$property(), $rules[$key]) === false) {
                    throw new Exception($message);
                }
            }
        }

        return true;
    }

    public function create()
    {
        if ($this->isFull() === true) {
            return false;
        }

        if (empty($this->create) === true) {

            // automatically accept new files, when "accept" is set
            if (empty($this->accept) === false) {
                return [
                    'content'  => [],
                    'name'     => null,
                    'template' => null,
                ];
            }

            return false;
        }

        $result = [
            'content'  => empty($this->create['content']) ? [] : $this->create['content'],
            'name'     => $this->create['name'] ?? null,
            'template' => $this->create['template'] ?? null
        ];

        return $result;
    }

    protected function defaultQuery(): string
    {
        return 'page.files';
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

            $name = (new Tempura($template, $data))->render();
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
        } else {
            $type = '/site/files/' . $item->filename();
        }
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

    public function query(): string
    {
        $query  = $this->query;

        if ($this->sortable() === true) {
            $query .= '.sortBy("sort", "asc")';
        }

        return $query;
    }

    public function upload(array $data)
    {
        // make sure the basics are provided
        if (isset($data['filename'], $data['source']) === false) {
            throw new Exception('Please provide a filename');
        }

        // get all create options from the blueprint
        $options = $this->create();

        // check if adding files is allowed at all
        if (empty($options)) {
            throw new Exception('No files can be added');
        }

        // make sure we don't allow more entries than accepted
        if ($this->isFull()) {
            throw new Exception('Too many files');
        }

        // validate the upload
        $this->accepts($data['source']);

        // merge the post data with the pre-defined content set in the blueprint
        $content = array_merge($data['content'] ?? [], $options['content']);

        return $this->parent()->createFile([
            'source'   => $data['source'],
            'content'  => $content,
            'template' => $options['template'],
            'filename' => $this->filename($data['source'], $data['filename'], $options['name'])
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

    protected function setAccept($accept = null)
    {
        if (is_string($accept) === true) {
            $accept = [
                'mime' => $accept
            ];
        }

        if (is_array($accept) === false && $accept !== null) {
            throw new Exception('Invalid accept rules definition');
        }

        $this->accept = $accept;
        return $this;
    }

    protected function setCreate(array $create = null)
    {
        $this->create = $create;
        return $this;
    }

    public function sort(array $input)
    {
        if ($this->sortable() === false) {
            throw new Exception('Files cannot be sorted');
        }

        $files = $this->parent()->files();

        foreach ($input as $index => $id) {
            $file = $files->findBy('id', $id);
            $file->changeSort($index + 1);
        }

        return true;
    }

}
