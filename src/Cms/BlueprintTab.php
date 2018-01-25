<?php

namespace Kirby\Cms;

use Exception;

class BlueprintTab extends BlueprintObject
{

    /**
     * All properties/methods that should be included
     * in BlueprintTab::toArray
     *
     * @var array
     */
    protected static $toArray = [
        'columns',
        'icon',
        'id',
        'label',
        'name'
    ];

    /**
     * A BlueprintCollection of all columns
     * in the tab
     *
     * @var array|BlueprintCollection
     */
    protected $columns;

    /**
     * The tab icon name
     *
     * @var string
     */
    protected $icon;

    /**
     * The tab's unique id
     *
     * @var string
     */
    protected $id;

    /**
     * The tab label
     *
     * @var string
     */
    protected $label;

    /**
     * The tab's unique name
     *
     * @var string
     */
    protected $name;

    /**
     * A BlueprintCollection of all
     * fields in the tab
     *
     * @var BlueprintCollection
     */
    protected $fields;

    /**
     * A BlueprintCollection of all
     * sections in the tab
     *
     * @var [type]
     */
    protected $sections;

    /**
     * Creates a new BlueprintTab object
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $props = $this->extend($props);

        $props = BlueprintConverter::convertFieldsToSection($props);
        $props = BlueprintConverter::convertSectionsToColumn($props);

        // properties
        $this->setRequiredProperties($props, ['columns', 'name']);
        $this->setOptionalProperties($props, ['icon', 'id', 'label']);
    }

    /**
     * Gets all columns in the tab.
     * If the columns are set as array,
     * converts them to a BlueprintCollecton first.
     *
     * @return BlueprintCollection
     */
    public function columns(): BlueprintCollection
    {
        if (is_a($this->columns, BlueprintCollection::class) === true) {
            return $this->columns;
        }

        $columns = new BlueprintCollection;

        foreach ($this->columns as $name => $props) {
            // use the key as name if the name is not set
            $props['name'] = $props['name'] ?? $name;
            $column = new BlueprintColumn($props);
            $columns->set($column->name(), $column);
        }

        return $this->columns = $columns;
    }

    /**
     * Returns a single field by name
     *
     * @param string $name
     * @return BlueprintField|null
     */
    public function field(string $name)
    {
        return $this->fields()->find($name);
    }

    /**
     * Gets all fields in the tab.
     * It walks through all columns and sections
     * to gather really all fields.
     *
     * @return BlueprintCollection
     */
    public function fields(): BlueprintCollection
    {
        if (is_a($this->fields, BlueprintCollection::class) === true) {
            return $this->fields;
        }

        $this->fields = new BlueprintCollection;

        foreach ($this->sections() as $section) {
            if (is_a($section->fields(), BlueprintCollection::class) === false) {
                continue;
            }

            foreach ($section->fields() as $field) {
                $this->fields->set($field->id(), $field);
            }
        }

        return $this->fields;
    }

    /**
     * Returns the tab icon name
     *
     * @return string
     */
    public function icon()
    {
        return $this->icon;
    }

    /**
     * Returns the tab id
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id ?? $this->name();
    }

    /**
     * Returns the tab label
     *
     * @return string
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * Returns the tab name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name ?? 'main';
    }

    /**
     * Returns a single section by name
     *
     * @param string $name
     * @return BlueprintSection|null
     */
    public function section(string $name)
    {
        return $this->sections()->find($name);
    }

    /**
     * Gathers all sections in the tab in a
     * BlueprintCollection object. The result
     * is cached in the $sections property
     *
     * @return BlueprintCollection
     */
    public function sections(): BlueprintCollection
    {
        if (is_a($this->sections, BlueprintCollection::class) === true) {
            return $this->sections;
        }

        $this->sections = new BlueprintCollection;

        foreach ($this->columns() as $column) {
            foreach ($column->sections() as $section) {
                $this->sections->set($section->id(), $section);
            }
        }

        return $this->sections;
    }

    /**
     * Setter for all columns in the tab
     * Columns are passed as array and then
     * converted to a BlueprintCollection by
     * the getter
     *
     * @param array $columns
     * @return self
     */
    protected function setColumns(array $columns): self
    {
        if (empty($columns) == true) {
            throw new Exception('Please define at least one column for the tab');
        }

        $this->columns = $columns;
        return $this;
    }

    /**
     * Sets the tab icon name
     *
     * @param string $icon
     * @return self
     */
    protected function setIcon(string $icon = null): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Sets the unique tab id
     * If no id is set, the getter will
     * return the unique name
     *
     * @param string $id
     * @return self
     */
    protected function setId(string $id = null): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the tab label
     *
     * @param string $label
     * @return self
     */
    protected function setLabel(string $label = null): self
    {
        $this->label = $this->i18n($label);
        return $this;
    }

    /**
     * Sets the unique tab name
     *
     * @param string $name
     * @return self
     */
    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Converts the tab object to an array
     * to be used as API response for example
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['columns'] = $this->columns()->toArray();

        return $array;
    }

    /**
     * The layout array is a reduced version,
     * which is specificially used for the frontend
     *
     * @return array
     */
    public function toLayout(): array
    {
        $array = parent::toArray();;
        $array['columns'] = $this->columns()->toLayout();

        return $array;
    }
}
