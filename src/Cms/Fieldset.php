<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Represents a single Fieldset
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Fieldset extends Item
{
    const ITEMS_CLASS = '\Kirby\Cms\Fieldsets';

    protected $disabled;
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
        $this->icon      = $params['icon'] ?? null;
        $this->model     = $this->parent;
        $this->kirby     = $this->parent->kirby();
        $this->name      = $this->createName($params['name'] ?? Str::ucfirst($this->type));
        $this->label     = $this->createLabel($params['label'] ?? null);
        $this->preview   = $params['preview'] ?? null;
        $this->tabs      = $this->createTabs($params);
        $this->translate = $params['translate'] ?? true;
        $this->unset     = $params['unset'] ?? false;
        $this->wysiwyg   = $params['wysiwyg'] ?? false;

        if (
            $this->translate === false &&
            $this->kirby->multilang() === true &&
            $this->kirby->language()->isDefault() === false
        ) {
            // disable and unset the fieldset if it's not translatable
            $this->unset    = true;
            $this->disabled = true;
        }
    }

    protected function createFields(array $fields = []): array
    {
        $fields = Blueprint::fieldsProps($fields);
        $fields = $this->form($fields)->fields()->toArray();

        // collect all fields
        $this->fields = array_merge($this->fields, $fields);

        return $fields;
    }

    protected function createName($name): string
    {
        return I18n::translate($name, $name);
    }

    protected function createLabel($label = null): ?string
    {
        return I18n::translate($label, $label);
    }

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
            $tab = Blueprint::extend($tab);

            $tab['fields'] = $this->createFields($tab['fields'] ?? []);
            $tab['label']  = $this->createLabel($tab['label'] ?? Str::ucfirst($name));
            $tab['name']   = $name;

            $tabs[$name] = $tab;
        }

        return $tabs;
    }

    public function disabled(): bool
    {
        return $this->disabled;
    }

    public function fields(): array
    {
        return $this->fields;
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

    public function icon(): ?string
    {
        return $this->icon;
    }

    public function label(): ?string
    {
        return $this->label;
    }

    public function model()
    {
        return $this->model;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function tabs(): array
    {
        return $this->tabs;
    }

    public function translate(): bool
    {
        return $this->translate;
    }

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
            'disabled'  => $this->disabled,
            'icon'      => $this->icon,
            'label'     => $this->label,
            'name'      => $this->name,
            'preview'   => $this->preview,
            'tabs'      => $this->tabs,
            'translate' => $this->translate,
            'type'      => $this->type,
            'unset'     => $this->unset,
            'wysiwyg'   => $this->wysiwyg,
        ];
    }

    public function unset(): bool
    {
        return $this->unset;
    }
}
