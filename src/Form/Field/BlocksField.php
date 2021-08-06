<?php

namespace Kirby\Form\Field;

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
use Throwable;

class BlocksField extends FieldClass
{
    use EmptyState;
    use Max;
    use Min;

    protected $blocks;
    protected $fields;
    protected $fieldsets;
    protected $forms;
    protected $group;
    protected $pretty;
    protected $value = [];

    public function __construct(array $params = [])
    {
        $this->setFieldsets($params['fieldsets'] ?? null, $params['model'] ?? site());

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
                $form = $this->form($type);
                $form->fill($block['content']);

                // overwrite the block content with form values
                $block['content'] = $form->$to();

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
        if (empty($this->fields[$type]) === false) {
            return $this->fields[$type];
        }

        return $this->fields[$type] = $this->fieldset($type)->fields();
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

    public function form(string $type)
    {
        if (empty($this->form[$type]) === false) {
            return $this->form[$type];
        }

        return $this->form[$type] = new Form([
            'fields' => $this->fields($type),
            'model'  => $this->model,
            'strict' => true,
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
                'action'  => function () {
                    return ['uuid' => uuid()];
                }
            ],
            [
                'pattern' => 'fieldsets/(:any)',
                'method'  => 'GET',
                'action'  => function ($fieldsetType) use ($field) {
                    $form = $field->form($fieldsetType);
                    $form->fill($form->data(true));

                    $content = $form->values();

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
                    $field    = $field->form($fieldsetType)->field($fieldName);
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

                $index = 0;

                foreach ($value as $block) {
                    $index++;
                    $blockType = $block['type'];

                    try {
                        $blockFields = $this->fields($blockType);
                    } catch (Throwable $e) {
                        // skip invalid blocks
                        continue;
                    }

                    $form = $this->form($blockType);
                    $form->fill($block['content']);

                    // overwrite the content with the serialized form
                    foreach ($form->fields() as $field) {
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
