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
	public const ITEMS_CLASS = Fieldsets::class;

	protected bool $disabled;
	protected bool $editable;
	protected array $fields = [];
	protected string|null $icon;
	protected string|null $label;
	protected string|null $name;
	protected string|bool|null $preview;
	protected array $tabs;
	protected bool $translate;
	protected string $type;
	protected bool $unset;
	protected bool $wysiwyg;

	/**
	 * Creates a new Fieldset object
	 */
	public function __construct(array $params = [])
	{
		if (empty($params['type']) === true) {
			throw new InvalidArgumentException('The fieldset type is missing');
		}

		$this->type = $params['id'] = $params['type'];

		parent::__construct($params);

		$this->disabled    = $params['disabled'] ?? false;
		$this->editable    = $params['editable'] ?? true;
		$this->icon        = $params['icon'] ?? null;
		$params['title'] ??= $params['name'] ?? Str::ucfirst($this->type);
		$this->name        = $this->createName($params['title']);
		$this->label       = $this->createLabel($params['label'] ?? null);
		$this->preview     = $params['preview'] ?? null;
		$this->tabs        = $this->createTabs($params);
		$this->translate   = $params['translate'] ?? true;
		$this->unset       = $params['unset'] ?? false;
		$this->wysiwyg     = $params['wysiwyg'] ?? false;

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

	protected function createFields(array $fields = []): array
	{
		$fields = Blueprint::fieldsProps($fields);
		$fields = $this->form($fields)->fields()->toArray();

		// collect all fields
		$this->fields = array_merge($this->fields, $fields);

		return $fields;
	}

	protected function createName(array|string $name): string|null
	{
		return I18n::translate($name, $name);
	}

	protected function createLabel(array|string|null $label = null): string|null
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
			// unset/remove tab if its property is false
			if ($tab === false) {
				unset($tabs[$name]);
				continue;
			}

			$tab = Blueprint::extend($tab);

			$tab['fields']  = $this->createFields($tab['fields'] ?? []);
			$tab['label'] ??= Str::ucfirst($name);
			$tab['label']   = $this->createLabel($tab['label']);
			$tab['name']    = $name;

			$tabs[$name] = $tab;
		}

		return $tabs;
	}

	public function disabled(): bool
	{
		return $this->disabled;
	}

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

	public function fields(): array
	{
		return $this->fields;
	}

	/**
	 * Creates a form for the given fields
	 */
	public function form(array $fields, array $input = []): Form
	{
		return new Form([
			'fields' => $fields,
			'model'  => $this->parent,
			'strict' => true,
			'values' => $input,
		]);
	}

	public function icon(): string|null
	{
		return $this->icon;
	}

	public function label(): string|null
	{
		return $this->label;
	}

	public function model(): ModelWithContent
	{
		return $this->parent;
	}

	public function name(): string
	{
		return $this->name;
	}

	public function preview(): string|bool|null
	{
		return $this->preview;
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

	public function unset(): bool
	{
		return $this->unset;
	}

	public function wysiwyg(): bool
	{
		return $this->wysiwyg;
	}
}
