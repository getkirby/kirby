<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
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
     * @param string $key
     * @param array $fieldset
     * @return array
     */
    public function fieldset(string $key, array $fieldset): array
    {
        return [
            'fields' => $this->fields($fieldset['fields'] ?? []),
            'key'    => $key,
            'name'   => $name = $fieldset['name'] ?? Str::ucfirst($key),
            'label'  => $fieldset['label'] ?? $name,
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

        foreach ($this->props['fieldsets'] ?? [] as $key => $fieldset) {
            $fieldset = $this->fieldset($key, $fieldset);
            $fieldsets[$fieldset['key']] = $fieldset;
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
     * Creates a unique ID for the block
     * based on the block type
     *
     * @param string $type
     * @return string
     */
    public function uid(string $type): string
    {
        return $type . '_' . time() . '_' . Str::random(6);
    }

    /**
     * @return array
     */
    public function value(): array
    {
        if ($this->value !== null) {
            return $this->value;
        }

        $value     = Data::decode($this->props['value'] ?? null, 'yaml');
        $fieldsets = $this->fieldsets();
        $result    = [];

        foreach ($value as $key => $block) {
            $type = $block['_key'] ?? null;
            $uid  = $block['_uid'] ?? $this->uid($type);

            // ignore fieldsets that don't exist
            if ($type === null || isset($fieldsets[$type]) === false) {
                continue;
            }

            $fieldset = $fieldsets[$type];
            $values   = $this->form($fieldset['fields'] ?? [], $block)->values();

            // add private row values
            $values['_key'] = $type;
            $values['_uid'] = $uid;

            $result[] = $values;
        }

        return $result;
    }
}
