<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Form\Fields;
use Kirby\Util\F;

/**
 * The Blueprint class converts an array from a
 * blueprint file into an object with a Kirby-style
 * chainable API for sections and everything
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
    protected $fields;

    /**
     * @var BlueprintTabs
     */
    protected $tabs;

    /**
     * The blueprint name
     *
     * @var string
     */
    protected $name;

    /**
     * Model option settings
     *
     * @var array
     */
    protected $options;

    /**
     * Cache for the sections collection
     *
     * @var BlueprintCollection
     */
    protected $sections;

    /**
     * The blueprint title
     *
     * @var string
     */
    protected $title;

    /**
     * Creates a new Blueprint and converts
     * the input to always return a valid nested array
     * of options.
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $props = $this->extend($props);
        $props = BlueprintConverter::convertFieldsToSection($props);
        $props = BlueprintConverter::convertSectionsToColumns($props);
        $props = BlueprintConverter::convertColumnsToTabs($props);

        $this->tabs = new BlueprintTabs($this, $props['tabs'] ?? []);

        $this->setProperties($props);
    }

    /**
     * Prepare the BlueprintTabs object for the
     * Blueprint::toArray method
     *
     * @return array
     */
    protected function convertTabsToArray(): array
    {
        return $this->tabs()->toArray();
    }

    /**
     * Convert the options object to array
     *
     * @return array
     */
    protected function convertOptionsToArray(): array
    {
        return $this->options()->toArray();
    }

    /**
     * Returns a specific field from the blueprint
     *
     * @param string $name
     * @return BlueprintField|null
     */
    public function field(string $name)
    {
        if ($field = $this->fields()->find($name)) {
            return $field;
        }

        throw new Exception('The field could not be found');
    }

    /**
     * Returns a collection of all Fields
     * in the blueprint
     *
     * @return BlueprintCollection
     */
    public function fields()
    {
        if (is_a($this->fields, BlueprintCollection::class) === true) {
            return $this->fields;
        }

        $fields = new Fields;

        foreach ($this->sections() as $section) {
            // skip sections without fields
            if (method_exists($section, 'fields') === false) {
                continue;
            }

            foreach ($section->fields() as $field) {
                $fields->set($field->name(), $field);
            }
        }

        return $this->fields = $fields;
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
     * @return BlueprintTabs
     */
    public function tabs(): BlueprintTabs
    {
        return $this->tabs;
    }

    /**
     * Find a blueprint by name
     *
     * @param string $name
     * @return string
     */
    public static function find(string $name): string
    {
        $kirby = App::instance();
        $root  = $kirby->root('blueprints');
        $file  = $root . '/' . $name . '.yml';

        if (F::exists($file, $root) === true) {
            return $file;
        }

        if ($file = $kirby->get('blueprint', $name)) {
            return $file;
        }

        throw new Exception(sprintf('The blueprint "%s" could not be loaded', $name));
    }

    /**
     * Loads a blueprint from file or array
     *
     * @param string $name
     * @param string $fallback
     * @param Model $model
     * @return self
     */
    public static function load(string $name, string $fallback = null, Model $model)
    {
        try {
            $file = static::find($name);
        } catch (Exception $e) {
            $file = $fallback !== null ? static::find($fallback) : null;
        }

        $data          = Data::read($file);
        $data['name']  = F::name($file);
        $data['model'] = $model;

        return new static($data);
    }

    /**
     * Returns the Blueprint name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns all model options
     *
     * @return array
     */
    public function options()
    {
        return $this->options ?? new BlueprintOptions($this->model(), $this->options);
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
        if ($section = $this->sections()->find($name)) {
            return $section;
        }

        throw new Exception(sprintf('The section "%s" could not be found', $name));
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

        $sections = new BlueprintCollection;

        foreach ((array)$this->sections as $name => $props) {
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
     * Returns the Blueprint title
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
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
     * @param array $options
     * @return self
     */
    protected function setOptions(array $options = null): self
    {
        $this->options = $options;
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
     * @param string|array $title
     * @return self
     */
    protected function setTitle($title): self
    {
        $this->title = $this->i18n($title);
        return $this;
    }

}
