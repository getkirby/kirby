<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Represents a single Fieldset
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Fieldset extends Item
{
    public const ITEMS_CLASS = '\Kirby\Cms\Fieldsets';

    protected $disabled;
    protected $editable;
    protected $fields = [];
    protected $icon;
    protected $label;
    protected $model;
    protected $name;
    protected $preview;
    protected $tabs;
    protected $translate;
    protected $type;
    protected $unset;
    protected $wysiwyg;

    /**
     * Creates a new Fieldset object
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (empty($params['type']) === true) {
            throw new InvalidArgumentException('The fieldset type is missing');
        }

        $this->type = $params['id'] = $params['type'];

        parent::__construct($params);

        $this->disabled  = $params['disabled'] ?? false;
        $this->editable  = $params['editable'] ?? true;
        $this->icon      = $params['icon'] ?? null;
        $this->model     = $this->parent;
        $this->name      = $this->createName($params['title'] ?? $params['name'] ?? Str::ucfirst($this->type));
        $this->label     = $this->createLabel($params['label'] ?? null);
        $this->preview   = $params['preview'] ?? null;
        $this->tabs      = $this->createTabs($params);
        $this->translate = $params['translate'] ?? true;
        $this->unset     = $params['unset'] ?? false;
        $this->wysiwyg   = $params['wysiwyg'] ?? false;

        if (
            $this->translate === false &&
            $this->kirby()->multilang() === true &&
            $this->kirby()->language()->isDefault() === false
        ) {
            // disable and unset the fieldset if it's not translatable
            $this->unset    = true;
            $this->disabled = true;
        }
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function createFields(array $fields = []): array
    {
        $fields = Blueprint::fieldsProps($fields);
        $fields = $this->form($fields)->fields()->toArray();

        // collect all fields
        $this->fields = array_merge($this->fields, $fields);

        return $fields;
    }

    /**
     * @param array|string $name
     * @return string|null
     */
    protected function createName($name): ?string
    {
        return I18n::translate($name, $name);
    }

    /**
     * @param array|string $label
     * @return string|null
     */
    protected function createLabel($label = null): ?string
    {
        return I18n::translate($label, $label);
    }

    /**
     * @param array $params
     * @return array
     */
    protected function createTabs(array $params = []): array
    {
        $tabs = $params['tabs'] ?? [];

        // return a single tab if there are only fields
        if (empty($tabs) === true) {
            return [
                'content' => [
                    'fields' => $this->createFields($params['fields'] ?? []),
                ]
            ];
        }

        // normalize tabs props
        foreach ($tabs as $name => $tab) {
            // unset/remove tab if its property is false
            if ($tab === false) {
                unset($tabs[$name]);
                continue;
            }

            $tab = Blueprint::extend($tab);

            $tab['fields'] = $this->createFields($tab['fields'] ?? []);
            $tab['label']  = $this->createLabel($tab['label'] ?? Str::ucfirst($name));
            $tab['name']   = $name;

            $tabs[$name] = $tab;
        }

        return $tabs;
    }

    /**
     * @return bool
     */
    public function disabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @return bool
     */
    public function editable(): bool
    {
        if ($this->editable === false) {
            return false;
        }

        if (count($this->fields) === 0) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * Creates a form for the given fields
     *
     * @param array $fields
     * @param array $input
     * @return \Kirby\Form\Form
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
     * @return string|null
     */
    public function icon(): ?string
    {
        return $this->icon;
    }

    /**
     * @return string|null
     */
    public function label(): ?string
    {
        return $this->label;
    }

    /**
     * @return \Kirby\Cms\ModelWithContent
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string|bool
     */
    public function preview()
    {
        return $this->preview;
    }

    /**
     * @return array
     */
    public function tabs(): array
    {
        return $this->tabs;
    }

    /**
     * @return bool
     */
    public function translate(): bool
    {
        return $this->translate;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'disabled'  => $this->disabled(),
            'editable'  => $this->editable(),
            'icon'      => $this->icon(),
            'label'     => $this->label(),
            'name'      => $this->name(),
            'preview'   => $this->preview(),
            'tabs'      => $this->tabs(),
            'translate' => $this->translate(),
            'type'      => $this->type(),
            'unset'     => $this->unset(),
            'wysiwyg'   => $this->wysiwyg(),
        ];
    }

    /**
     * @return bool
     */
    public function unset(): bool
    {
        return $this->unset;
    }

    /**
     * @return bool
     */
    public function wysiwyg(): bool
    {
        return $this->wysiwyg;
    }
}
