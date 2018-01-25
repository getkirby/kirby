<?php

namespace Kirby\Cms;

use Exception;

class BlueprintField extends BlueprintObject
{

    use HasUnknownProperties;

    /**
     * All properties that should be
     * converted to an array in
     * BlueprintField::toArray()
     *
     * @var array
     */
    protected static $toArray = [
        'id',
        'label',
        'name',
        'type'
    ];

    /**
     * The field id
     *
     * The getter will fall back to the
     * name if no id is set
     *
     * @var string
     */
    protected $id;

    /**
     * The field label
     *
     * This is translatable and can
     * be defined as an associative array
     * with multiple translations
     *
     * @var array|string
     */
    protected $label;

    /**
     * The field name
     * This is required!
     *
     * @var string
     */
    protected $name;

    /**
     * The field type
     * This is required!
     *
     * @var string
     */
    protected $type;

    /**
     * @param array $props
     */
    public function __construct(array $props)
    {
        // extend field props
        $props = $this->extend($props);

        // properties
        $this->setRequiredProperties($props, ['name', 'type']);
        $this->setOptionalProperties($props, ['id', 'label']);
        $this->setUnknownProperties($props);
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id ?? $this->name();
    }

    /**
     * @return string
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @param string $id
     * @return self
     */
    protected function setId(string $id = null): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string|array $label
     * @return self
     */
    protected function setLabel($label = null): self
    {
        $this->label = $this->i18n($label);
        return $this;
    }

    /**
     * @param string $name
     * @return self
     */
    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $type
     * @return self
     */
    protected function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

}
