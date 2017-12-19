<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;

/**
 * The Blueprint class converts an array from a
 * blueprint file into an object with a Kirby-style
 * chainable API for tabs, sections and everything
 * else
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Blueprint extends BlueprintObject
{

    /**
     * Cache for the fields collection
     *
     * @var BlueprintCollection
     */
    protected $fields = null;

    /**
     * Cache for the sections collection
     *
     * @var BlueprintCollection
     */
    protected $sections = null;

    /**
     * Cache for the tabs collection
     *
     * @var BlueprintCollection
     */
    protected $tabs = null;

    /**
     * Creates a new Blueprint and converts
     * the input to always return a valid nested array
     * of options.
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $props = BlueprintConverter::convertFieldsToSection($props);
        $props = BlueprintConverter::convertSectionsToColumn($props);
        $props = BlueprintConverter::convertColumnsToTab($props);

        parent::__construct($props);
    }

    /**
     * Returns a collection of all Fields
     * in the blueprint
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
            foreach ($section->fields() as $field) {
                $this->fields->set($field->id(), $field);
            }
        }

        return $this->fields;
    }

    /**
     * Returns a specific field from the blueprint
     *
     * @param string $name
     * @return BlueprintField|null
     */
    public function field(string $name)
    {
        return $this->fields()->find($name);
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
     * @param string|array $input
     * @return self
     */
    public static function load($input)
    {
        if (is_array($input)) {
            return new static($input);
        }

        if (is_file($input) === false) {
            throw new Exception('The blueprint cannot be found');
        }

        $data         = Data::read($input);
        $data['name'] = pathinfo($input, PATHINFO_FILENAME);

        return new static($data);
    }

    /**
     * Returns an object of options
     *
     * @return BlueprintObject
     */
    public function options()
    {
        return new BlueprintObject($this->prop('options'));
    }

    /**
     * Returns a specific BlueprintSection object
     * by name, if it exists
     *
     * @param string $name
     * @return BlueprintSection|null
     */
    public function section(string $name)
    {
        return $this->sections()->find($name);
    }

    /**
     * Returns a collection of all sections
     *
     * @return BlueprintCollection
     */
    public function sections(): BlueprintCollection
    {
        if (is_a($this->sections, BlueprintCollection::class) === true) {
            return $this->sections;
        }

        $this->sections = new BlueprintCollection;

        foreach ($this->tabs() as $tab) {
            foreach ($tab->sections() as $section) {
                $this->sections->append($section->id(), $section);
            }
        }

        return $this->sections;
    }

    /**
     * Returns the prop schema
     *
     * @return array
     */
    public function schema(): array
    {
        return [
            'name' => [
                'type'     => 'string',
                'required' => true
            ],
            'options' => [
                'type'     => 'array',
                'required' => false,
                'default'  => []
            ],
            'tabs' => [
                'type'     => 'array',
                'required' => true
            ],
            'title' => [
                'type'     => 'string',
                'required' => true
            ],
        ];
    }

    /**
     * Returns a specific BlueprintTab object
     * by name, if it exists
     *
     * @param string $name
     * @return BlueprintTab|null
     */
    public function tab(string $name)
    {
        return $this->tabs()->find($name);
    }

    /**
     * Returns a BlueprintCollection of all
     * BlueprintTab objects.
     *
     * @return BlueprintCollection
     */
    public function tabs(): Collection
    {
        if (is_a($this->tabs, BlueprintCollection::class) === true) {
            return $this->tabs;
        }

        $this->tabs = new BlueprintCollection();

        foreach ($this->prop('tabs') as $name => $props) {
            // use the key as name if the name is not set
            $props['name'] = $props['name'] ?? $name;
            $tab = new BlueprintTab($props);
            $this->tabs->append($tab->name(), $tab);
        }

        return $this->tabs;
    }

    /**
     * Converts the Blueprint and all nested Objects
     * to a nested, associative array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['tabs'] = $this->tabs()->toArray();

        return $array;
    }

    /**
     * Returns a simplified array for the
     * panel layout.
     *
     * @return array
     */
    public function toLayout(): array
    {
        return $this->tabs()->toLayout();
    }

}
