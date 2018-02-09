<?php

namespace Kirby\Cms;

use Exception;

/**
 * Class BlueprintColumn
 *
 * @package Kirby\Cms
 */
class BlueprintColumn extends BlueprintObject
{

    /**
     * Properties that should be
     * included in BlueprintColumn::toArray
     *
     * @var array
     */
    protected static $toArray = [
        'id',
        'name',
        'sections',
        'width'
    ];

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array|BlueprintCollection
     */
    protected $sections;

    /**
     * @var string
     */
    protected $width = '1/1';

    /**
     * BlueprintColumn constructor.
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $props = $this->extend($props);

        // convert simpler blueprint layouts
        $props = BlueprintConverter::convertFieldsToSection($props);

        // properties
        $this->setProperties($props);
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
    public function name(): string
    {
        return $this->name;
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
     * @return BlueprintCollection
     */
    public function sections(): BlueprintCollection
    {
        if (is_a($this->sections, BlueprintCollection::class) === true) {
            return $this->sections;
        }

        $sections = new BlueprintCollection;

        foreach ($this->sections as $name => $props) {

            // use the key as name if the name is not set
            $props['name'] = $props['name'] ?? $name;

            // pass down the model
            $props['model'] = $this->model();

            $section = BlueprintSection::factory($props);
            $sections->set($section->id(), $section);

        }

        return $this->sections = $sections;
    }

    /**
     * @return string
     */
    public function width(): string
    {
        return $this->width ?? '1/1';
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
     * @param string $name
     * @return self
     */
    protected function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array $sections
     * @return self
     */
    protected function setSections(array $sections = null): self
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * @param string $width
     * @return self
     */
    protected function setWidth(string $width = null): self
    {
        if ($width !== null && in_array($width, ['1/1', '1/2', '1/3', '2/3']) === false) {
            throw new Exception('Invalid width value');
        }

        $this->width = $width;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['sections'] = $this->sections()->toArray();

        return $array;
    }

    /**
     * @return array
     */
    public function toLayout(): array
    {
        $array = parent::toLayout();
        $array['sections'] = $this->sections()->toLayout();

        return $array;
    }

}
