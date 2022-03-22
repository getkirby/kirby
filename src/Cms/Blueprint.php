<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Form\Field;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Throwable;

/**
 * The Blueprint class normalizes an array from a
 * blueprint file and converts sections, columns, fields
 * etc. into a correct tab layout.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Blueprint
{
    public static $presets = [];
    public static $loaded = [];

    protected $fields = [];
    protected $model;
    protected $props;
    protected $sections = [];
    protected $tabs = [];

    /**
     * Magic getter/caller for any blueprint prop
     *
     * @param string $key
     * @param array|null $arguments
     * @return mixed
     */
    public function __call(string $key, array $arguments = null)
    {
        return $this->props[$key] ?? null;
    }

    /**
     * Creates a new blueprint object with the given props
     *
     * @param array $props
     * @throws \Kirby\Exception\InvalidArgumentException If the blueprint model is missing
     */
    public function __construct(array $props)
    {
        if (empty($props['model']) === true) {
            throw new InvalidArgumentException('A blueprint model is required');
        }

        if (is_a($props['model'], ModelWithContent::class) === false) {
            throw new InvalidArgumentException('Invalid blueprint model');
        }

        $this->model = $props['model'];

        // the model should not be included in the props array
        unset($props['model']);

        // extend the blueprint in general
        $props = $this->extend($props);

        // apply any blueprint preset
        $props = $this->preset($props);

        // normalize the name
        $props['name'] ??= 'default';

        // normalize and translate the title
        $props['title'] = $this->i18n($props['title'] ?? ucfirst($props['name']));

        // convert all shortcuts
        $props = $this->convertFieldsToSections('main', $props);
        $props = $this->convertSectionsToColumns('main', $props);
        $props = $this->convertColumnsToTabs('main', $props);

        // normalize all tabs
        $props['tabs'] = $this->normalizeTabs($props['tabs'] ?? []);

        $this->props = $props;
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->props ?? [];
    }

    /**
     * Converts all column definitions, that
     * are not wrapped in a tab, into a generic tab
     *
     * @param string $tabName
     * @param array $props
     * @return array
     */
    protected function convertColumnsToTabs(string $tabName, array $props): array
    {
        if (isset($props['columns']) === false) {
            return $props;
        }

        // wrap everything in a main tab
        $props['tabs'] = [
            $tabName => [
                'columns' => $props['columns']
            ]
        ];

        unset($props['columns']);

        return $props;
    }

    /**
     * Converts all field definitions, that are not
     * wrapped in a fields section into a generic
     * fields section.
     *
     * @param string $tabName
     * @param array $props
     * @return array
     */
    protected function convertFieldsToSections(string $tabName, array $props): array
    {
        if (isset($props['fields']) === false) {
            return $props;
        }

        // wrap all fields in a section
        $props['sections'] = [
            $tabName . '-fields' => [
                'type'   => 'fields',
                'fields' => $props['fields']
            ]
        ];

        unset($props['fields']);

        return $props;
    }

    /**
     * Converts all sections that are not wrapped in
     * columns, into a single generic column.
     *
     * @param string $tabName
     * @param array $props
     * @return array
     */
    protected function convertSectionsToColumns(string $tabName, array $props): array
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
     * Extends the props with props from a given
     * mixin, when an extends key is set or the
     * props is just a string
     *
     * @param array|string $props
     * @return array
     */
    public static function extend($props): array
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
            $mixin = static::find($extends);
            $mixin = static::extend($mixin);
            $props = A::merge($mixin, $props, A::MERGE_REPLACE);
        } catch (Exception $e) {
            // keep the props unextended if the snippet wasn't found
        }

        // remove the extends flag
        unset($props['extends']);

        return $props;
    }

    /**
     * Create a new blueprint for a model
     *
     * @param string $name
     * @param string|null $fallback
     * @param \Kirby\Cms\Model $model
     * @return static|null
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
     * Returns a single field definition by name
     *
     * @param string $name
     * @return array|null
     */
    public function field(string $name): ?array
    {
        return $this->fields[$name] ?? null;
    }

    /**
     * Returns all field definitions
     *
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * Find a blueprint by name
     *
     * @param string $name
     * @return array
     * @throws \Kirby\Exception\NotFoundException If the blueprint cannot be found
     */
    public static function find(string $name): array
    {
        if (isset(static::$loaded[$name]) === true) {
            return static::$loaded[$name];
        }

        $kirby = App::instance();
        $root  = $kirby->root('blueprints');
        $file  = $root . '/' . $name . '.yml';

        // first try to find a site blueprint,
        // then check in the plugin extensions
        if (F::exists($file, $root) !== true) {
            $file = $kirby->extension('blueprints', $name);
        }

        // now ensure that we always return the data array
        if (is_string($file) === true && F::exists($file) === true) {
            return static::$loaded[$name] = Data::read($file);
        } elseif (is_array($file) === true) {
            return static::$loaded[$name] = $file;
        } elseif (is_callable($file) === true) {
            return static::$loaded[$name] = $file($kirby);
        }

        // neither a valid file nor array data
        throw new NotFoundException([
            'key'  => 'blueprint.notFound',
            'data' => ['name' => $name]
        ]);
    }

    /**
     * Used to translate any label, heading, etc.
     *
     * @param mixed $value
     * @param mixed $fallback
     * @return mixed
     */
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
     * @return array
     */
    public static function load(string $name): array
    {
        $props = static::find($name);

        $normalize = function ($props) use ($name) {
            // inject the filename as name if no name is set
            $props['name'] ??= $name;

            // normalize the title
            $title = $props['title'] ?? ucfirst($props['name']);

            // translate the title
            $props['title'] = I18n::translate($title, $title);

            return $props;
        };

        return $normalize($props);
    }

    /**
     * Returns the parent model
     *
     * @return \Kirby\Cms\Model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Returns the blueprint name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->props['name'];
    }

    /**
     * Normalizes all required props in a column setup
     *
     * @param string $tabName
     * @param array $columns
     * @return array
     */
    protected function normalizeColumns(string $tabName, array $columns): array
    {
        foreach ($columns as $columnKey => $columnProps) {
            // unset/remove column if its property is not array
            if (is_array($columnProps) === false) {
                unset($columns[$columnKey]);
                continue;
            }

            $columnProps = $this->convertFieldsToSections($tabName . '-col-' . $columnKey, $columnProps);

            // inject getting started info, if the sections are empty
            if (empty($columnProps['sections']) === true) {
                $columnProps['sections'] = [
                    $tabName . '-info-' . $columnKey => [
                        'headline' => 'Column (' . ($columnProps['width'] ?? '1/1') . ')',
                        'type'     => 'info',
                        'text'     => 'No sections yet'
                    ]
                ];
            }

            $columns[$columnKey] = array_merge($columnProps, [
                'width'    => $columnProps['width'] ?? '1/1',
                'sections' => $this->normalizeSections($tabName, $columnProps['sections'] ?? [])
            ]);
        }

        return $columns;
    }

    /**
     * @param array $items
     * @return string
     */
    public static function helpList(array $items): string
    {
        $md = [];

        foreach ($items as $item) {
            $md[] = '- *' . $item . '*';
        }

        return PHP_EOL . implode(PHP_EOL, $md);
    }

    /**
     * Normalize field props for a single field
     *
     * @param array|string $props
     * @return array
     * @throws \Kirby\Exception\InvalidArgumentException If the filed name is missing or the field type is invalid
     */
    public static function fieldProps($props): array
    {
        $props = static::extend($props);

        if (isset($props['name']) === false) {
            throw new InvalidArgumentException('The field name is missing');
        }

        $name = $props['name'];
        $type = $props['type'] ?? $name;

        if ($type !== 'group' && isset(Field::$types[$type]) === false) {
            throw new InvalidArgumentException('Invalid field type ("' . $type . '")');
        }

        // support for nested fields
        if (isset($props['fields']) === true) {
            $props['fields'] = static::fieldsProps($props['fields']);
        }

        // groups don't need all the crap
        if ($type === 'group') {
            return [
                'fields' => $props['fields'],
                'name'   => $name,
                'type'   => $type,
            ];
        }

        // add some useful defaults
        return array_merge($props, [
            'label' => $props['label'] ?? ucfirst($name),
            'name'  => $name,
            'type'  => $type,
            'width' => $props['width'] ?? '1/1',
        ]);
    }

    /**
     * Creates an error field with the given error message
     *
     * @param string $name
     * @param string $message
     * @return array
     */
    public static function fieldError(string $name, string $message): array
    {
        return [
            'label' => 'Error',
            'name'  => $name,
            'text'  => strip_tags($message),
            'theme' => 'negative',
            'type'  => 'info',
        ];
    }

    /**
     * Normalizes all fields and adds automatic labels,
     * types and widths.
     *
     * @param array $fields
     * @return array
     */
    public static function fieldsProps($fields): array
    {
        if (is_array($fields) === false) {
            $fields = [];
        }

        foreach ($fields as $fieldName => $fieldProps) {

            // extend field from string
            if (is_string($fieldProps) === true) {
                $fieldProps = [
                    'extends' => $fieldProps,
                    'name'    => $fieldName
                ];
            }

            // use the name as type definition
            if ($fieldProps === true) {
                $fieldProps = [];
            }

            // unset / remove field if its property is false
            if ($fieldProps === false) {
                unset($fields[$fieldName]);
                continue;
            }

            // inject the name
            $fieldProps['name'] = $fieldName;

            // create all props
            try {
                $fieldProps = static::fieldProps($fieldProps);
            } catch (Throwable $e) {
                $fieldProps = static::fieldError($fieldName, $e->getMessage());
            }

            // resolve field groups
            if ($fieldProps['type'] === 'group') {
                if (empty($fieldProps['fields']) === false && is_array($fieldProps['fields']) === true) {
                    $index  = array_search($fieldName, array_keys($fields));
                    $before = array_slice($fields, 0, $index);
                    $after  = array_slice($fields, $index + 1);
                    $fields = array_merge($before, $fieldProps['fields'] ?? [], $after);
                } else {
                    unset($fields[$fieldName]);
                }
            } else {
                $fields[$fieldName] = $fieldProps;
            }
        }

        return $fields;
    }

    /**
     * Normalizes blueprint options. This must be used in the
     * constructor of an extended class, if you want to make use of it.
     *
     * @param array|true|false|null|string $options
     * @param array $defaults
     * @param array $aliases
     * @return array
     */
    protected function normalizeOptions($options, array $defaults, array $aliases = []): array
    {
        // return defaults when options are not defined or set to true
        if ($options === true) {
            return $defaults;
        }

        // set all options to false
        if ($options === false) {
            return array_map(fn () => false, $defaults);
        }

        // extend options if possible
        $options = $this->extend($options);

        foreach ($options as $key => $value) {
            $alias = $aliases[$key] ?? null;

            if ($alias !== null) {
                $options[$alias] ??= $value;
                unset($options[$key]);
            }
        }

        return array_merge($defaults, $options);
    }

    /**
     * Normalizes all required keys in sections
     *
     * @param string $tabName
     * @param array $sections
     * @return array
     */
    protected function normalizeSections(string $tabName, array $sections): array
    {
        foreach ($sections as $sectionName => $sectionProps) {

            // unset / remove section if its property is false
            if ($sectionProps === false) {
                unset($sections[$sectionName]);
                continue;
            }

            // fallback to default props when true is passed
            if ($sectionProps === true) {
                $sectionProps = [];
            }

            // inject all section extensions
            $sectionProps = $this->extend($sectionProps);

            $sections[$sectionName] = $sectionProps = array_merge($sectionProps, [
                'name' => $sectionName,
                'type' => $type = $sectionProps['type'] ?? $sectionName
            ]);

            if (empty($type) === true || is_string($type) === false) {
                $sections[$sectionName] = [
                    'name' => $sectionName,
                    'headline' => 'Invalid section type for section "' . $sectionName . '"',
                    'type' => 'info',
                    'text' => 'The following section types are available: ' . $this->helpList(array_keys(Section::$types))
                ];
            } elseif (isset(Section::$types[$type]) === false) {
                $sections[$sectionName] = [
                    'name' => $sectionName,
                    'headline' => 'Invalid section type ("' . $type . '")',
                    'type' => 'info',
                    'text' => 'The following section types are available: ' . $this->helpList(array_keys(Section::$types))
                ];
            }

            if ($sectionProps['type'] === 'fields') {
                $fields = Blueprint::fieldsProps($sectionProps['fields'] ?? []);

                // inject guide fields guide
                if (empty($fields) === true) {
                    $fields = [
                        $tabName . '-info' => [
                            'label' => 'Fields',
                            'text'  => 'No fields yet',
                            'type'  => 'info'
                        ]
                    ];
                } else {
                    foreach ($fields as $fieldName => $fieldProps) {
                        if (isset($this->fields[$fieldName]) === true) {
                            $this->fields[$fieldName] = $fields[$fieldName] = [
                                'type'  => 'info',
                                'label' => $fieldProps['label'] ?? 'Error',
                                'text'  => 'The field name <strong>"' . $fieldName . '"</strong> already exists in your blueprint.',
                                'theme' => 'negative'
                            ];
                        } else {
                            $this->fields[$fieldName] = $fieldProps;
                        }
                    }
                }

                $sections[$sectionName]['fields'] = $fields;
            }
        }

        // store all normalized sections
        $this->sections = array_merge($this->sections, $sections);

        return $sections;
    }

    /**
     * Normalizes all required keys in tabs
     *
     * @param array $tabs
     * @return array
     */
    protected function normalizeTabs($tabs): array
    {
        if (is_array($tabs) === false) {
            $tabs = [];
        }

        foreach ($tabs as $tabName => $tabProps) {

            // unset / remove tab if its property is false
            if ($tabProps === false) {
                unset($tabs[$tabName]);
                continue;
            }

            // inject all tab extensions
            $tabProps = $this->extend($tabProps);

            // inject a preset if available
            $tabProps = $this->preset($tabProps);

            $tabProps = $this->convertFieldsToSections($tabName, $tabProps);
            $tabProps = $this->convertSectionsToColumns($tabName, $tabProps);

            $tabs[$tabName] = array_merge($tabProps, [
                'columns' => $this->normalizeColumns($tabName, $tabProps['columns'] ?? []),
                'icon'    => $tabProps['icon']  ?? null,
                'label'   => $this->i18n($tabProps['label'] ?? ucfirst($tabName)),
                'link'    => $this->model->panel()->url(true) . '/?tab=' . $tabName,
                'name'    => $tabName,
            ]);
        }

        return $this->tabs = $tabs;
    }

    /**
     * Injects a blueprint preset
     *
     * @param array $props
     * @return array
     */
    protected function preset(array $props): array
    {
        if (isset($props['preset']) === false) {
            return $props;
        }

        if (isset(static::$presets[$props['preset']]) === false) {
            return $props;
        }

        $preset = static::$presets[$props['preset']];

        if (is_string($preset) === true) {
            $preset = require $preset;
        }

        return $preset($props);
    }

    /**
     * Returns a single section by name
     *
     * @param string $name
     * @return \Kirby\Cms\Section|null
     */
    public function section(string $name)
    {
        if (empty($this->sections[$name]) === true) {
            return null;
        }

        // get all props
        $props = $this->sections[$name];

        // inject the blueprint model
        $props['model'] = $this->model();

        // create a new section object
        return new Section($props['type'], $props);
    }

    /**
     * Returns all sections
     *
     * @return array
     */
    public function sections(): array
    {
        return A::map(
            $this->sections,
            fn ($section) => $this->section($section['name'])
        );
    }

    /**
     * Returns a single tab by name
     *
     * @param string|null $name
     * @return array|null
     */
    public function tab(?string $name = null): ?array
    {
        if ($name === null) {
            return A::first($this->tabs);
        }

        return $this->tabs[$name] ?? null;
    }

    /**
     * Returns all tabs
     *
     * @return array
     */
    public function tabs(): array
    {
        return array_values($this->tabs);
    }

    /**
     * Returns the blueprint title
     *
     * @return string
     */
    public function title(): string
    {
        return $this->props['title'];
    }

    /**
     * Converts the blueprint object to a plain array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->props;
    }
}
