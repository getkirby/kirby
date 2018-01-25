<?php

namespace Kirby\Cms;

use Exception;

class BlueprintSection extends BlueprintObject
{

    use HasUnknownProperties;

    /**
     * All properties that should be
     * included in BlueprintSection::toArray
     *
     * @var array
     */
    protected static $toArray = [
        'id',
        'name',
        'type'
    ];

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array|BlueprintCollection
     */
    protected $fields;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * Creates a new BlueprintSection object
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $props = $this->extend($props);

        $this->setRequiredProperties($props, ['name', 'type']);
        $this->setOptionalProperties($props, ['fields', 'id']);
        $this->setUnknownProperties($props);
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
     * Returns all fields in the section
     *
     * @return BlueprintCollection
     */
    public function fields(): BlueprintCollection
    {
        if (is_a($this->fields, BlueprintCollection::class) === true) {
            return $this->fields;
        }

        $fields = new BlueprintCollection;

        foreach ((array)$this->fields as $name => $props) {
            // use the key as name if the name is not set
            $props['name'] = $props['name'] ?? $name;
            $field = new BlueprintField($props);
            $fields->set($field->name(), $field);
        }

        return $this->fields = $fields;
    }

    /**
     * Gets the value of id
     * Will fall back to the name
     *
     * @return string
     */
    public function id(): string
    {
        return $this->id ?? $this->name();
    }

    /**
     * Gets the value of name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Gets the value of type
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Sets the value of id
     *
     * @return self
     */
    protected function setId($id = null): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Sets the fields
     *
     * @return self
     */
    protected function setFields(array $fields = []): self
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * Sets the value of name
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
     * Sets the value of type
     *
     * @param string|null $type
     * @return  self
     */
    protected function setType(string $type = null): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Converts the section object to a handy
     * array i.e. for API results
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        if ($this->type() === 'fields') {
            $array['fields'] = $this->fields()->toArray();
        }

        ksort($array);

        return $array;
    }

    /**
     * @return string
     */
    public function toLayout()
    {
        return $this->id();
    }

}
