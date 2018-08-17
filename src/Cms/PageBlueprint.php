<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Data\Data;
use Kirby\Toolkit\F;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Obj;

class PageBlueprintFoundation
{

    public static $presets = [];
    public static $loaded = [];

    protected $fields = [];
    protected $model;
    protected $props;
    protected $sections = [];
    protected $tabs = [];

    public function __call(string $key, array $arguments = null)
    {
        return $this->props[$key] ?? null;
    }

    public function __construct(array $props)
    {
        if (empty($props['name']) === true) {
            throw new InvalidArgumentException('A blueprint name is required');
        }

        if (empty($props['model']) === true) {
            throw new InvalidArgumentException('A blueprint model is required');
        }

        $this->model = $props['model'];

        // the model should not be included in the props array
        unset($props['model']);

        // extend the blueprint in general
        $props = $this->extend($props);

        // apply any blueprint preset
        $props = $this->preset($props);

        // normalize and translate the title
        $props['title'] = $this->i18n($props['title'] ?? $props['name']);

        // convert all shortcuts
        $props = $this->convertFieldsToSections($props);
        $props = $this->convertSectionsToColumns($props);
        $props = $this->convertColumnsToTabs($props);

        // normalize all props
        $props['tabs'] = $this->normalizeTabs($props['tabs'] ?? []);

        $this->props = $props;
    }

    public function __debuginfo()
    {
        return $this->props;
    }

    protected function convertColumnsToTabs($props)
    {
        if (isset($props['columns']) === false) {
            return $props;
        }

        // wrap everything in a main tab
        $props['tabs'] = [
            'main' => [
                'columns' => $props['columns']
            ]
        ];

        unset($props['columns']);

        return $props;
    }

    protected function convertFieldsToSections($props)
    {
        if (isset($props['fields']) === false) {
            return $props;
        }

        // wrap all fields in a section
        $props['sections'] = [
            'content' => [
                'type'   => 'fields',
                'fields' => $props['fields']
            ]
        ];

        unset($props['fields']);

        return $props;
    }

    protected function convertSectionsToColumns($props)
    {
        if (isset($props['sections']) === false) {
            return $props;
        }

        // wrap everything in one big column
        $props['columns'] = [
            [
                'width'    => '1/1',
                'sections' => $props['sections']
            ]
        ];

        unset($props['sections']);

        return $props;
    }

    /**
     * @param array|string $props
     * @return array
     */
    public function extend($props): array
    {
        if (is_string($props) === true) {
            $props = [
                'extends' => $props
            ];
        }

        $extends = $props['extends'] ?? null;

        if ($extends === null) {
            return $props;
        }

        try {
            $mixin = Data::read(App::instance()->root('blueprints') . '/' . $extends . '.yml');
        } catch (Exception $e) {
            $mixin = [];
        }

        return array_replace_recursive($mixin, $props);
    }

    public function field(string $name): ?array
    {
        return $this->field[$name] ?? null;
    }

    public function fields()
    {
        return new Obj($this->fields);
    }

    /**
     * Find a blueprint by name
     *
     * @param string $name
     * @return string|array
     */
    public static function find(string $name)
    {
        $kirby = App::instance();
        $root  = $kirby->root('blueprints');
        $file  = $root . '/' . $name . '.yml';

        if (F::exists($file, $root) === true) {
            return $file;
        }

        if ($blueprint = $kirby->extension('blueprints', $name)) {
            return $blueprint;
        }

        throw new NotFoundException([
            'key'  => 'blueprint.notFound',
            'data' => ['name' => $name]
        ]);
    }

    protected function i18n($value, $fallback = null)
    {
        return I18n::translate($value, $fallback ?? $value);
    }

    /**
     * Checks if this is the default blueprint
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->name() === 'default';
    }

    /**
     * Loads a blueprint from file or array
     *
     * @param string $name
     * @param string $fallback
     * @param Model $model
     * @return array
     */
    public static function load(string $name)
    {
        if (isset(static::$loaded[$name]) === true) {
            return static::$loaded[$name];
        }

        $props = static::find($name);

        if (is_array($props) === true) {
            return $props;
        }

        $file  = $props;
        $props = Data::read($file);

        // inject the filename as name if no name is set
        $props['name'] = $props['name'] ?? F::name($file);

        return static::$loaded[$name] = $props;
    }

    public function model()
    {
        return $this->model;
    }

    public function name(): string
    {
        return $this->props['name'];
    }

    protected function normalizeColumns(array $columns): array
    {
        foreach ($columns as $columnKey => $columnProps) {

            $columnProps = $this->convertFieldsToSections($columnProps);

            $columns[$columnKey] = array_merge($columnProps, [
                'width'    => $columnProps['width'] ?? '1/1',
                'sections' => $this->normalizeSections($columnProps['sections'] ?? [])
            ]);
        }

        return $columns;
    }

    protected function normalizeFields(array $fields): array
    {
        foreach ($fields as $fieldName => $fieldProps) {

            // inject all field extensions
            $fieldProps = $this->extend($fieldProps);

            $fields[$fieldName] = $fieldProps = array_merge($fieldProps, [
                'label' => $fieldProps['label'] ?? ucfirst($fieldName),
                'name'  => $fieldName,
                'type'  => $fieldProps['type'] ?? null,
                'width' => $fieldProps['width'] ?? '1/1',
            ]);

            // check for valid field types here

        }

        // store all normalized fields
        $this->fields = array_merge($this->fields, $fields);

        return $fields;
    }

    protected function normalizeSections(array $sections): array
    {
        foreach ($sections as $sectionName => $sectionProps) {

            // inject all section extensions
            $sectionProps = $this->extend($sectionProps);

            $sections[$sectionName] = $sectionProps = array_merge($sectionProps, [
                'name' => $sectionName,
                'type' => $sectionProps['type'] ?? null
            ]);

            // TODO: check for a correct section type here …

            if ($sectionProps['type'] === 'fields') {
                $sections[$sectionName]['fields'] = $this->normalizeFields($sectionProps['fields'] ?? []);
            }

        }

        // store all normalized sections
        $this->sections = array_merge($this->sections, $sections);

        return $sections;
    }

    protected function normalizeTabs(array $tabs): array
    {
        foreach ($tabs as $tabName => $tabProps) {

            // inject all tab extensions
            $tabProps = $this->extend($tabProps);

            // inject a preset if available
            $tabProps = $this->preset($tabProps);

            $tabProps = $this->convertFieldsToSections($tabProps);
            $tabProps = $this->convertSectionsToColumns($tabProps);

            $tabs[$tabName] = array_merge($tabProps, [
                'columns' => $this->normalizeColumns($tabProps['columns'] ?? []),
                'icon'    => $tabProps['icon']  ?? null,
                'label'   => $this->i18n($tabProps['label'] ?? $tabName),
                'name'    => $tabName,
            ]);
        }

        return $this->tabs = $tabs;
    }

    protected function preset(array $props): array
    {

        if (isset($props['preset']) === false) {
            return $props;
        }

        if (isset(static::$presets[$props['preset']]) === false) {
            return $props;
        }

        return static::$presets[$props['preset']]($props);
    }

    public function section(string $name)
    {

        $props = $this->sections[$name] ?? null;

        if ($props === null) {
            return null;
        }

        $props['model'] = $this->model();

        try {
            return BlueprintSection::factory($props);
        } catch (Exception $e) {
            return BlueprintSection::factory([
                'headline' => 'Error',
                'model'    => $this->model(),
                'name'     => $props['name'],
                'type'     => 'info',
                'theme'    => 'negative',
                'text'     => $e->getMessage(),
            ]);
        }

    }

    public function sections(): array
    {
        return $this->sections;
    }

    public function tab(string $name): ?array
    {
        return $this->tabs[$name] ?? null;
    }

    public function tabs(): array
    {
        return $this->tabs;
    }

    public function title(): string
    {
        return $this->props['title'];
    }

    public function toArray(): array
    {
        return $this->props;
    }

}


class PageBlueprint extends PageBlueprintFoundation
{

    protected $options;

    public function __construct($props)
    {
        parent::__construct($props);

        $this->props = $this->setNum($this->props);
        $this->props = $this->setStatus($this->props);
    }

    /**
     * Create a new blueprint for a model
     *
     * @param string $name
     * @param string $fallback
     * @param Model $model
     * @return self
     */
    public static function factory(string $name, string $fallback = null, Model $model)
    {
        try {
            $props = static::load($name);
        } catch (Exception $e) {
            $props = $fallback !== null ? static::load($fallback) : null;
        }

        if ($props === null) {
            return null;
        }

        // inject the parent model
        $props['model'] = $model;

        return new static($props);
    }

    /**
     * Returns the page numbering mode
     *
     * @return string
     */
    public function num()
    {
        return $this->props['num'];
    }

    /**
     * Returns the options object
     * that handles page options and permissions
     *
     * @return PageBlueprintOptions
     */
    public function options()
    {
        if (is_a($this->options, 'Kirby\Cms\PageBlueprintOptions') === true) {
            return $this->options;
        }

        return $this->options = new PageBlueprintOptions($this->model(), $this->props['options'] ?? []);
    }

    protected function setNum($props): array
    {
        $num     = $props['num'] ?? 'default';
        $aliases = [
            0          => 'zero',
            'date'     => '{{ page.date("Ymd") }}',
            'datetime' => '{{ page.date("YmdHi") }}',
            'sort'     => 'default',
        ];

        $props['num'] = $aliases[$num] ?? 'default';

        return $props;
    }

    protected function setStatus($props): array
    {

        $status   = $props['status'] ?? null;
        $defaults = [
            'draft'    => [
                'label' => I18n::translate('page.status.draft'),
                'text'  => I18n::translate('page.status.draft.description'),
            ],
            'unlisted' => [
                'label' => I18n::translate('page.status.unlisted'),
                'text'  => I18n::translate('page.status.unlisted.description'),
            ],
            'listed' => [
                'label' => I18n::translate('page.status.listed'),
                'text'  => I18n::translate('page.status.listed.description'),
            ]
        ];

        // use the defaults, when the status is not defined
        if (is_array($status) === false) {
            $status = $defaults;
        }

        // clean up and translate each status
        foreach ($status as $key => $options) {

            // skip invalid status definitions
            if (in_array($key, ['draft', 'listed', 'unlisted']) === false) {
                continue;
            }

            // convert everything to a simple array
            if (is_array($options) === false) {
                $status[$key] = [
                    'label' => $options,
                    'text'  => null
                ];
            }

            // always make sure to have a proper label
            if (empty($status[$key]['label']) === true) {
                $status[$key]['label'] = $defaults[$key]['label'];
            }

            // also make sure to have the text field set
            if (isset($status[$key]['text']) === false) {
                $status[$key]['text'] = null;
            }

            // translate text and label if necessary
            $status[$key]['label'] = I18n::translate($status[$key]['label'], $status[$key]['label']);
            $status[$key]['text']  = I18n::translate($status[$key]['text'], $status[$key]['text']);
        }

        // the draft status is required
        if (isset($status['draft']) === false) {
            $status = ['draft' => $defaults['draft']] + $status;
        }

        $props['status'] = $status;

        return $props;

    }

    public function status(): array
    {
        return $this->props['status'];
    }

    public function toArray(): array
    {
        $props = parent::toArray();
        $props['options'] = $this->options()->toArray();

        return $props;
    }

}
