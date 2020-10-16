<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * The Builder class is handling all the
 * complicated tasks for the builder field
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Builder
{
    /**
     * @var null|array
     */
    protected $fieldsets;

    /**
     * @var \Kirby\Cms\ModelWithContent
     */
    protected $model;

    /**
     * @var array
     */
    protected $props;

    /**
     * @var array
     */
    protected $value;

    /**
     * @param ModelWithContent $model
     * @param array $props
     */
    public function __construct(ModelWithContent $model, array $props = [])
    {
        $this->model = $model;
        $this->props = $props;
    }

    /**
     * @param array|string $blocks
     * @return \Kirby\Cms\Blocks
     */
    public function blocks($blocks)
    {
        $blocks = Blocks::parse($blocks, 'builder');
        return Blocks::factory($blocks['blocks'], ['type' => 'builder']);
    }

    /**
     * Return all fields in a fieldset
     *
     * @param string $type
     * @return array
     */
    public function fields(string $type): array
    {
        $fieldset = $this->fieldset($type);
        $fields   = [];

        foreach ($fieldset['tabs'] as $tab) {
            $fields = array_merge($fields, $tab['fields']);
        }

        return $fields;
    }

    /**
     * Return a fieldset by type
     *
     * @param string $type
     * @return array
     * @throws \Kirby\Exception\NotFoundException
     */
    public function fieldset(string $type): array
    {
        $fieldsets = $this->fieldsets();

        if (isset($fieldsets[$type]) === false) {
            throw new NotFoundException('The fieldset type could not be found');
        }

        return $fieldsets[$type];
    }

    /**
     * Expand and return all fieldsets for the builder
     *
     * @return array
     */
    public function fieldsets(): array
    {
        if ($this->fieldsets !== null) {
            return $this->fieldsets;
        }

        $fieldsets = [];
        $kirby = App::instance();

        if ($kirby->multilang() === true) {
            $languageCode      = $kirby->language()->code();
            $isDefaultLanguage = $languageCode === $kirby->defaultLanguage()->code();
        }

        foreach ($this->props['fieldsets'] ?? [] as $type => $fieldset) {
            $fieldset = $this->fieldsetProps($type, Blueprint::extend($fieldset));

            // switch untranslatable fieldset to readonly
            if ($fieldset['translate'] === false && ($isDefaultLanguage ?? true) === false) {
                $fieldset['unset']    = true;
                $fieldset['disabled'] = true;
            }

            $fieldsets[$fieldset['type']] = $fieldset;
        }

        return $this->fieldsets = $fieldsets;
    }

    /**
     * Returns all props for the fields
     * after being processed by the
     * form including all the heavy lifting
     * of extensions, groups, nested fields etc.
     *
     * @param array $fields
     * @return array
     */
    public function fieldsProps(array $fields): array
    {
        $fields = Blueprint::fieldsProps($fields);
        return $this->form($fields)->fields()->toArray();
    }

    /**
     * @param string $type
     * @param array $fieldset
     * @return array
     */
    public function fieldsetProps(string $type, array $fieldset): array
    {
        $fieldset['name'] = $fieldset['name'] ?? Str::ucfirst($type);

        return [
            'disabled'  => $fieldset['disabled'] ?? false,
            'icon'      => $fieldset['icon'] ?? null,
            'label'     => I18n::translate($label = $fieldset['label'] ?? $fieldset['name'], $label),
            'name'      => $fieldset['name'],
            'tabs'      => $this->tabsProps($fieldset),
            'translate' => $fieldset['translate'] ?? null,
            'type'      => $type,
            'unset'     => $fieldset['unset'] ?? false,
        ];
    }

    /**
     * Creates a form for the given fields
     *
     * @param array $fields
     * @param array $input
     * @return \Kirby\Cms\Form
     */
    public function form(array $fields, array $input = [])
    {
        return new Form([
            'fields' => $fields,
            'model'  => $this->model,
            'strict' => true,
            'values' => $input,
        ]);
    }

    /**
     * @param array $fieldset
     * @return array
     */
    public function tabsProps(array $fieldset): array
    {
        $tabs = $fieldset['tabs'] ?? [];

        // return a single tab if there are only fields
        if (empty($tabs) === true) {
            return [
                'content' => [
                    'fields' => $this->fieldsProps($fieldset['fields'] ?? []),
                ]
            ];
        }

        // normalize tabs props
        foreach ($tabs as $name => $tab) {
            $tab = Blueprint::extend($tab);

            $tab['fields'] = $this->fieldsProps($tab['fields'] ?? []);
            $tab['label']  = I18n::translate($label = $tab['label'] ?? Str::ucfirst($name), $label);
            $tab['name']   = $name;

            $tabs[$name] = $tab;
        }

        return $tabs;
    }

    /**
     * @param array|null $blocks
     * @param bool $pretty
     * @return void
     */
    public function toJson(array $blocks = null, bool $pretty = true)
    {
        $fields = [];

        foreach ((array)$blocks as $index => $block) {
            $blockType   = $block['type'];
            $blockFields = $fields[$blockType] ?? $this->fields($blockType) ?? [];

            // store the fields for the next round
            $fields[$blockType] = $blockFields;

            // overwrite the content with the serialized form
            $blocks[$index]['content'] = $this->form($blockFields, $block['content'])->content();
        }

        $value = [
            'type'   => 'builder',
            'blocks' => $blocks
        ];

        if ($pretty === true) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return json_encode($value);
    }

    /**
     * @param array $value
     * @return bool
     */
    public function validate(array $value = null)
    {
        $blocks = $this->blocks($value);
        $max    = $this->props['max'] ?? null;
        $min    = $this->props['min'] ?? null;
        $fields = [];

        if ($min && $blocks->count() < $min) {
            throw new InvalidArgumentException([
                'key'  => 'builder.blocks.min.' . ($min === 1 ? 'singular' : 'plural'),
                'data' => [
                    'min' => $min
                ]
            ]);
        }

        if ($max && $blocks->count() > $max) {
            throw new InvalidArgumentException([
                'key'  => 'builder.blocks.max.' . ($max === 1 ? 'singular' : 'plural'),
                'data' => [
                    'max' => $max
                ]
            ]);
        }

        foreach ($blocks as $block) {
            $blockType   = $block->type();
            $blockFields = $fields[$blockType] ?? $this->fields($blockType) ?? [];

            // store the fields for the next round
            $fields[$blockType] = $blockFields;

            // overwrite the content with the serialized form
            foreach ($this->form($blockFields, $block->content()->toArray())->fields() as $field) {
                $errors = $field->errors();

                // rough first validation
                if (empty($errors) === false) {
                    throw new InvalidArgumentException([
                        'key' => 'builder.validation',
                        'data' => [
                            'index' => $block->indexOf() + 1,
                        ]
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function value(): array
    {
        if ($this->value !== null) {
            return $this->value;
        }

        $value     = $this->props['value'] ?? [];
        $blocks    = $this->blocks($value)->toArray();

        $fieldsets = $this->fieldsets();
        $result    = [];

        foreach ($blocks as $block) {
            $type = $block['type'];
            $id   = $block['id'];

            // ignore fieldsets that don't exist
            if ($type === null || isset($fieldsets[$type]) === false) {
                continue;
            }

            // replace the block content with sanitized values
            $block['content'] = $this->form($this->fields($type), $block['content'])->values();

            $result[] = $block;
        }

        return $this->value = $result;
    }
}
