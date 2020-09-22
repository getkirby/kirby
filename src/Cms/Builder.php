<?php

namespace Kirby\Cms;

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
     * Returns all props for the fields
     * after being processed by the
     * form including all the heavy lifting
     * of extensions, groups, nested fields etc.
     *
     * @param array $fieldsProps
     * @return array
     */
    public function fields(array $fields): array
    {
        return $this->form($this->fieldsProps($fields))->fields()->toArray();
    }

    /**
     * @param string $type
     * @param array $fieldset
     * @return array
     */
    public function fieldset(string $type, array $fieldset): array
    {
        return [
            'fields'    => $this->fields($fieldset['fields'] ?? []),
            'type'      => $type,
            'name'      => $name = $fieldset['name'] ?? Str::ucfirst($type),
            'label'     => $fieldset['label'] ?? $name,
            'icon'      => $fieldset['icon'] ?? null,
            'disabled'  => $fieldset['disabled'] ?? false,
            'translate' => $fieldset['translate'] ?? null,
            'unset'     => $fieldset['unset'] ?? false,
        ];
    }

    /**
     * @param array $fieldsets
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
            $fieldset = $this->fieldset($type, Blueprint::extend($fieldset));

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
     * @param array $fields
     * @return array
     */
    public function fieldsProps(array $fields): array
    {
        return Blueprint::fieldsProps($fields);
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

            $fieldset = $fieldsets[$type];

            // replace the block content with sanitized values
            $block['content'] = $this->form($fieldset['fields'] ?? [], $block['content'])->values();

            $result[] = $block;
        }

        return $this->value = $result;
    }
}
