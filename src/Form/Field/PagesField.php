<?php

namespace Kirby\Form\Field;

use Kirby\Cms\Blueprint;
use Kirby\Panel\Collector\PagesCollector;
use Kirby\Panel\Controller\Dialog\PageCreateDialogController;
use Kirby\Panel\Ui\Item\PageItem;
use Kirby\Toolkit\A;
use Throwable;

/**
 * Pages field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class PagesField extends ModelsField
{
	/**
	 * Optional array of templates that should only be allowed to add
	 * or `false` to completely disable page creation
	 */
	protected array|false|null $create;

	/**
	 * Filters pages by their status. Available status settings: `draft`, `unlisted`, `listed`, `published`, `all`.
	 */
	protected string|null $status;

	/**
	 * Filters the list by single template.
	 */
	protected array|string|null $template;

	/**
	 * Filters the list by templates and sets template options
	 * when adding new pages to the section.
	 */
	protected array|null $templates;

	/**
	 * Excludes the selected templates.
	 */
	protected array|string|null $templatesIgnore;

	public function __construct(
		bool|null $batch = null,
		array|null $columns = null,
		array|false|null $create = null,
		array|string|null $empty = null,
		bool|null $flip = null,
		array|false|null $image = null,
		array|string|null $label = null,
		array|string|null $help = null,
		array|string|null $info = null,
		string|null $layout = null,
		int|null $limit = null,
		int|null $max = null,
		int|null $min = null,
		string|null $name = null,
		int|null $page = null,
		string|null $parent = null,
		string|null $query = null,
		bool|null $rawvalues = null,
		bool|null $search = null,
		string|null $size = null,
		bool|null $sortable = null,
		string|null $sortBy = null,
		string|null $status = null,
		array|string|null $template = null,
		array|null $templates = null,
		array|string|null $templatesIgnore = null,
		array|string|null $text = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			batch:     $batch,
			columns:   $columns,
			empty:     $empty,
			flip:      $flip,
			image:     $image,
			info:      $info,
			label:     $label,
			layout:    $layout,
			limit:     $limit,
			max:       $max,
			min:       $min,
			help:      $help,
			name:      $name,
			page:      $page,
			parent:    $parent,
			query:     $query,
			rawvalues: $rawvalues,
			search:    $search,
			size:      $size,
			sortable:  $sortable,
			sortBy:    $sortBy,
			text:      $text,
			when:      $when,
			width:     $width
		);

		$this->create          = $create;
		$this->status          = $status;
		$this->template        = $template;
		$this->templates       = $templates;
		$this->templatesIgnore = $templatesIgnore;
	}

	public function add(): bool
	{
		if ($this->create() === false) {
			return false;
		}

		if ($this->max() && $this->max() >= $this->total()) {
			return false;
		}

		// form here on, we need to check with which status
		// the pages are created and if the section can show
		// these newly created pages

		// if the section shows pages no matter what status they have,
		// we can always show the add button
		if ($this->status() === 'all') {
			return true;
		}

		// collect all statuses of the blueprints
		// that are allowed to be created
		$statuses = [];

		foreach ($this->blueprintNames() as $blueprint) {
			try {
				$props      = Blueprint::load('pages/' . $blueprint);
				$statuses[] = $props['create']['status'] ?? 'draft';
			} catch (Throwable) {
				$statuses[] = 'draft'; // @codeCoverageIgnore
			}
		}

		$statuses = array_unique($statuses);

		// if there are multiple statuses or if the section is showing
		// a different status than new pages would be created with,
		// we cannot show the add button
		if (count($statuses) > 1 || $this->status !== $statuses[0]) {
			return false;
		}

		return true;
	}

	public function blueprints(): array
	{
		$blueprints = [];

		// convert every template to a usable option array
		// for the template select box
		foreach ($this->blueprintNames() as $blueprint) {
			try {
				$props = Blueprint::load('pages/' . $blueprint);

				$blueprints[] = [
					'name'  => basename($props['name']),
					'title' => $props['title'],
				];
			} catch (Throwable) {
				$blueprints[] = [
					'name'  => basename($blueprint),
					'title' => ucfirst($blueprint),
				];
			}
		}

		return $blueprints;
	}

	public function blueprintNames(): array
	{
		$blueprints  = empty($this->create()) === false ? A::wrap($this->create()) : $this->templates();

		if (empty($blueprints) === true) {
			$blueprints = $this->kirby()->blueprints();
		}

		// excludes ignored templates
		if ($templatesIgnore = $this->templatesIgnore()) {
			$blueprints = array_diff($blueprints, $templatesIgnore);
		}

		return $blueprints;
	}

	public function collector(): PagesCollector
	{
		return $this->collector ??= new PagesCollector(
			limit: $this->limit(),
			page: $this->page() ?? 1,
			parent: $this->parentModel(),
			query: $this->query(),
			status: $this->status(),
			templates: $this->templates(),
			templatesIgnore: $this->templatesIgnore(),
			search: $this->searchterm(),
			sortBy: $this->sortBy(),
			flip: $this->flip()
		);
	}

	public function columns(): array
	{
		return [
			...parent::columns(),
			'flag' => [
				'label'  => ' ',
				'mobile' => true,
				'type'   => 'flag',
				'width'  => 'var(--table-row-height)',
			]
		];
	}

	public function create(): array|false
	{
		return $this->create ?? [];
	}

	public function dialogs(): array
	{
		return [
			'create' => [
				'action' => fn () => new PageCreateDialogController(
					parent: $this->parentModel(),
					field:  $this
				),
			]
		];
	}

	public function items(): array
	{
		$items = [];

		foreach ($this->models(paginated: true) as $page) {
			$item = (new PageItem(
				page:   $page,
				image:  $this->image(),
				layout: $this->layout(),
				info:   $this->info(),
				text:   $this->text(),
			))->props();

			if ($this->layout === 'table') {
				$item = $this->columnsValues($item, $page);
			}

			$items[] = $item;
		}

		return $items;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'add' => $this->add()
		];
	}

	public function status(): string
	{
		if ($this->status === 'drafts') {
			return 'draft';
		}

		if (in_array($this->status, ['all', 'draft', 'published', 'listed', 'unlisted'], true) === false) {
			return 'all';
		}

		return $this->status;
	}

	public function sortable(): bool
	{
		if (in_array($this->status(), ['listed', 'published', 'all'], true) === false) {
			return false;
		}

		return parent::sortable();
	}

	public function templates(): array
	{
		return array_filter(A::wrap($this->templates ?? $this->template));
	}

	public function templatesIgnore(): array
	{
		return array_filter(A::wrap($this->templatesIgnore));
	}
}
