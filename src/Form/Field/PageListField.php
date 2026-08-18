<?php

namespace Kirby\Form\Field;

use Kirby\Blueprint\Blueprint;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Interface\ProvidesAcceptedBlueprints;
use Kirby\Panel\Collector\PagesCollector;
use Kirby\Panel\Controller\Dialog\PageCreateDialogController;
use Kirby\Panel\Ui\Item\PageItem;
use Kirby\Toolkit\A;
use Throwable;

/**
 * Lists the children of a model
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelListField<Page>
 */
class PageListField extends ModelListField implements ProvidesAcceptedBlueprints
{
	public const string TYPE = 'pages';

	/**
	 * Templates that may be added or `false` to disable page creation
	 */
	protected array|string|bool|null $create;

	/**
	 * Filters the pages by their status
	 */
	protected string|null $status;

	/**
	 * Filters the list by a single template
	 */
	protected string|array|null $template;

	/**
	 * Filters the list by templates and sets the template
	 * options for newly created pages
	 */
	protected array|string|null $templates;

	/**
	 * Excludes the given templates from the list
	 */
	protected array|string|null $templatesIgnore;

	public function __construct(
		bool|null $batch = null,
		array|null $columns = null,
		array|string|bool|null $create = null,
		array|string|null $empty = null,
		bool|null $flip = null,
		array|string|null $help = null,
		array|string|false|null $image = null,
		array|string|null $info = null,
		array|string|null $label = null,
		string|null $layout = null,
		int|null $limit = null,
		int|null $max = null,
		int|null $min = null,
		string|null $name = null,
		int|null $page = null,
		string|null $parent = null,
		string|null $query = null,
		bool|null $search = null,
		string|null $size = null,
		bool|null $sortable = null,
		string|null $sortBy = null,
		string|null $status = null,
		string|array|null $template = null,
		array|string|null $templates = null,
		array|string|null $templatesIgnore = null,
		array|string|null $text = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			batch:    $batch,
			columns:  $columns,
			empty:    $empty,
			flip:     $flip,
			help:     $help,
			image:    $image,
			info:     $info,
			label:    $label,
			layout:   $layout,
			limit:    $limit,
			max:      $max,
			min:      $min,
			name:     $name,
			page:     $page,
			parent:   $parent,
			query:    $query,
			search:   $search,
			size:     $size,
			sortable: $sortable,
			sortBy:   $sortBy,
			text:     $text,
			when:     $when,
			width:    $width
		);

		$this->create          = $create;
		$this->status          = $status;
		$this->template        = $template;
		$this->templates       = $templates;
		$this->templatesIgnore = $templatesIgnore;
	}

	/**
	 * Whether new pages can be added to the list
	 */
	public function add(): bool
	{
		if ($this->create() === false || $this->isFull() === true) {
			return false;
		}

		// a list of all statuses shows every new page
		if ($this->status() === 'all') {
			return true;
		}

		$statuses = [];

		foreach ($this->blueprintNames() as $blueprint) {
			try {
				$props      = Blueprint::load('pages/' . $blueprint);
				$statuses[] = $props['create']['status'] ?? 'draft';
			} catch (Throwable) {
				$statuses[] = 'draft';
			}
		}

		$statuses = array_unique($statuses);

		// new pages would not show up in the list
		return count($statuses) === 1 && $this->status() === $statuses[0];
	}

	/**
	 * The blueprint options for the create dialog
	 */
	public function blueprints(): array
	{
		$blueprints = [];

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
		$blueprints = match (empty($this->create)) {
			false   => A::wrap($this->create),
			default => $this->templates()
		};

		if ($blueprints === []) {
			$blueprints = $this->kirby()->blueprints();
		}

		return array_diff($blueprints, $this->templatesIgnore());
	}

	public function collector(): PagesCollector
	{
		$parent = $this->parentModel();

		return $this->collector ??= new PagesCollector(
			flip:            $this->flip(),
			limit:           $this->limit(),
			page:            (int)$this->page() ?: 1,
			parent:          $parent,
			query:           $this->query(),
			search:          $this->searchterm(),
			sortBy:          $this->sortBy(),
			status:          $this->status(),
			templates:       $this->templates(),
			templatesIgnore: $this->templatesIgnore(),
		);
	}

	public function create(): array|string|bool|null
	{
		return $this->create;
	}

	/**
	 * The table layout shows the page status in its own column
	 */
	protected function defineColumns(): array
	{
		$columns = parent::defineColumns();

		if ($this->layout() !== 'table') {
			return $columns;
		}

		$columns['flag'] = [
			'label'  => ' ',
			'mobile' => true,
			'type'   => 'flag',
			'width'  => 'var(--table-row-height)',
		];

		return $columns;
	}

	// @codeCoverageIgnoreStart
	public function dialogs(): array
	{
		return [
			'create' => [
				'action' => fn () => new PageCreateDialogController(
					parent:     $this->parentModel(),
					blueprints: $this->blueprints()
				)
			]
		];
	}
	// @codeCoverageIgnoreEnd

	/**
	 * @throws InvalidArgumentException
	 */
	public function parentModel(): Page|Site
	{
		$parent = parent::parentModel();

		if ($parent instanceof Page === false && $parent instanceof Site === false) {
			throw new InvalidArgumentException(
				message: 'The parent is invalid. You must choose the site or a page as parent.'
			);
		}

		return $parent;
	}

	public function sortable(): bool
	{
		// only listed pages carry a sort number
		if (in_array($this->status(), ['listed', 'published', 'all'], true) === false) {
			return false;
		}

		return parent::sortable();
	}

	public function state(): array
	{
		return [
			...parent::state(),
			'add' => $this->add()
		];
	}

	public function status(): string
	{
		return match ($this->status) {
			'draft', 'drafts' => 'draft',
			'listed'          => 'listed',
			'published'       => 'published',
			'unlisted'        => 'unlisted',
			default           => 'all'
		};
	}

	public function template(): string|array|null
	{
		return $this->template;
	}

	public function templates(): array
	{
		return A::wrap($this->templates ?? $this->template);
	}

	public function templatesIgnore(): array
	{
		return A::wrap($this->templatesIgnore);
	}

	public function text(): string
	{
		return parent::text() ?? '{{ model.title }}';
	}

	/**
	 * @param Page $model
	 */
	public function toItem(ModelWithContent $model): array
	{
		return (new PageItem(
			page:   $model,
			image:  $this->image(),
			info:   $this->info(),
			layout: $this->layout(),
			text:   $this->text(),
		))->props();
	}
}
