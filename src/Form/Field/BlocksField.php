<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Blocks as BlocksCollection;
use Kirby\Cms\Fieldsets;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Form\Mixin\EmptyState;
use Kirby\Form\Mixin\Max;
use Kirby\Form\Mixin\Min;
use Kirby\Toolkit\Str;
use Throwable;

class BlocksField extends FieldClass
{
    use EmptyState;
    use Max;
    use Min;

    protected $blocks;
    protected $fieldsets;
    protected $group;
    protected $pretty;
    protected $value = [];

    public function __construct(array $params = [])
    {
        $this->setFieldsets($params['fieldsets'] ?? null, $params['model'] ?? App::instance()->site());

        parent::__construct($params);

        $this->setEmpty($params['empty'] ?? null);
        $this->setGroup($params['group'] ?? 'blocks');
        $this->setMax($params['max'] ?? null);
        $this->setMin($params['min'] ?? null);
        $this->setPretty($params['pretty'] ?? false);
    }

    public function blocksToValues($blocks, $to = 'values'): array
    {
        $result = [];
        $fields = [];

        foreach ($blocks as $block) {
            try {
                $type = $block['type'];

                // get and cache fields at the same time
                $fields[$type] ??= $this->fields($block['type']);

                // overwrite the block content with form values
                $block['content'] = $this->form($fields[$type], $block['content'])->$to();

                $result[] = $block;
            } catch (Throwable $e) {
                $result[] = $block;

                // skip invalid blocks
                continue;
            }
        }

        return $result;
    }

    public function fields(string $type)
    {
        return $this->fieldset($type)->fields();
    }

    public function fieldset(string $type)
    {
        if ($fieldset = $this->fieldsets->find($type)) {
            return $fieldset;
        }

        throw new NotFoundException('The fieldset ' . $type . ' could not be found');
    }

    public function fieldsets()
    {
        return $this->fieldsets;
    }

    public function fieldsetGroups(): ?array
    {
        $fieldsetGroups = $this->fieldsets()->groups();
        return empty($fieldsetGroups) === true ? null : $fieldsetGroups;
    }

    public function fill($value = null)
    {
        $value  = BlocksCollection::parse($value);
        $blocks = BlocksCollection::factory($value);
        $this->value = $this->blocksToValues($blocks->toArray());
    }

    public function form(array $fields, array $input = [])
    {
        return new Form([
            'fields' => $fields,
            'model'  => $this->model,
            'strict' => true,
            'values' => $input,
        ]);
    }

    public function isEmpty(): bool
    {
        return count($this->value()) === 0;
    }

    public function group(): string
    {
        return $this->group;
    }

    public function pretty(): bool
    {
        return $this->pretty;
    }

    public function props(): array
    {
        return [
            'empty'          => $this->empty(),
            'fieldsets'      => $this->fieldsets()->toArray(),
            'fieldsetGroups' => $this->fieldsetGroups(),
            'group'          => $this->group(),
            'max'            => $this->max(),
            'min'            => $this->min(),
        ] + parent::props();
    }

    public function routes(): array
    {
        $field = $this;

        return [
            [
                'pattern' => 'uuid',
                'action'  => fn () => ['uuid' => Str::uuid()]
            ],
            [
                'pattern' => 'paste',
                'method'  => 'POST',
                'action'  => function () use ($field) {
                    $request = App::instance()->request();

                    $value  = BlocksCollection::parse($request->get('html'));
                    $blocks = BlocksCollection::factory($value);
                    return $field->blocksToValues($blocks->toArray());
                }
            ],
            [
                'pattern' => 'fieldsets/(:any)',
                'method'  => 'GET',
                'action'  => function ($fieldsetType) use ($field) {
                    $fields   = $field->fields($fieldsetType);
                    $defaults = $field->form($fields, [])->data(true);
                    $content  = $field->form($fields, $defaults)->values();

                    return Block::factory([
                        'content' => $content,
                        'type'    => $fieldsetType
                    ])->toArray();
                }
            ],
            [
                'pattern' => 'fieldsets/(:any)/fields/(:any)/(:all?)',
                'method'  => 'ALL',
                'action'  => function (string $fieldsetType, string $fieldName, string $path = null) use ($field) {
                    $fields = $field->fields($fieldsetType);
                    $field  = $field->form($fields)->field($fieldName);

                    $fieldApi = $this->clone([
                        'routes' => $field->api(),
                        'data'   => array_merge($this->data(), ['field' => $field])
                    ]);

                    return $fieldApi->call($path, $this->requestMethod(), $this->requestData());
                }
            ],
        ];
    }

    public function store($value)
    {
        $blocks = $this->blocksToValues((array)$value, 'content');

        // returns empty string to avoid storing empty array as string `[]`
        // and to consistency work with `$field->isEmpty()`
        if (empty($blocks) === true) {
            return '';
        }

        return $this->valueToJson($blocks, $this->pretty());
    }

    protected function setFieldsets($fieldsets, $model)
    {
        if (is_string($fieldsets) === true) {
            $fieldsets = [];
        }

        $this->fieldsets = Fieldsets::factory($fieldsets, [
            'parent' => $model
        ]);
    }

    protected function setGroup(string $group = null)
    {
        $this->group = $group;
    }

    protected function setPretty(bool $pretty = false)
    {
        $this->pretty = $pretty;
    }

    public function validations(): array
    {
        return [
            'blocks' => function ($value) {
                if ($this->min && count($value) < $this->min) {
                    throw new InvalidArgumentException([
                        'key'  => 'blocks.min.' . ($this->min === 1 ? 'singular' : 'plural'),
                        'data' => [
                            'min' => $this->min
                        ]
                    ]);
                }

                if ($this->max && count($value) > $this->max) {
                    throw new InvalidArgumentException([
                        'key'  => 'blocks.max.' . ($this->max === 1 ? 'singular' : 'plural'),
                        'data' => [
                            'max' => $this->max
                        ]
                    ]);
                }

                $fields = [];
                $index  = 0;

                foreach ($value as $block) {
                    $index++;
                    $blockType = $block['type'];

                    try {
                        $blockFields = $fields[$blockType] ?? $this->fields($blockType) ?? [];
                    } catch (Throwable $e) {
                        // skip invalid blocks
                        continue;
                    }

                    // store the fields for the next round
                    $fields[$blockType] = $blockFields;

                    // overwrite the content with the serialized form
                    foreach ($this->form($blockFields, $block['content'])->fields() as $field) {
                        $errors = $field->errors();

                        // rough first validation
                        if (empty($errors) === false) {
                            throw new InvalidArgumentException([
                                'key' => 'blocks.validation',
                                'data' => [
                                    'index' => $index,
                                ]
                            ]);
                        }
                    }
                }

                return true;
            }
        ];
    }
}
