<?php

namespace Kirby\Form\Field;

use Kirby\Blueprint\PageBlueprint;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Interface\ProvidesAcceptedPageBlueprints;
use Kirby\Panel\Collector\PagesCollector;
use Kirby\Panel\Controller\Dialog\PageCreateDialogController;
use Kirby\Panel\Ui\Item\PageItem;
use Kirby\Reflection\Attributes\Prop;
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
class PageListField extends ModelListField implements ProvidesAcceptedPageBlueprints
{
	public const string TYPE = 'pages';

	#[Prop(export: false)]
	protected array|string|null $text = '{{ model.title }}';

	/**
	 * Blueprints that may be added or `false` to disable page creation
	 */
	#[Prop(export: false)]
	protected array|string|bool|null $create = null;

	/**
	 * Filters the pages by their status
	 */
	#[Prop(export: false)]
	protected string $status = 'all';

	/**
	 * Filters the list by one or more blueprints, which are also
	 * the options for new pages. With `except`, all other
	 * blueprints are listed and offered instead.
	 */
	#[Prop(aliases: ['templates', 'template'], export: false)]
	protected array|string|null $blueprints = null;

	/**
	 * Alias for the `except` list of the `blueprints` option
	 */
	#[Prop(export: false)]
	protected array|string $templatesIgnore = [];

	/**
	 * Accepts pages with the blueprints from `create`,
	 * the list's blueprints or any blueprint otherwise
	 *
	 * @return list<string>
	 */
	public function acceptedPageBlueprints(): array
	{
		// no pages can be added through the list
		if ($this->create === false) {
			return [];
		}

		$list  = $this->blueprints();
		$names = match ($this->create) {
			null, true, '', [] => $list['only'] ?? $this->kirby()->blueprints(),
			default            => A::wrap($this->create)
		};

		return array_values(array_diff($names, $list['except']));
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

		foreach ($this->acceptedPageBlueprints() as $name) {
			$blueprint  = $this->blueprint($name);
			$statuses[] = $blueprint?->create()['status'] ?? 'draft';
		}

		$statuses = array_unique($statuses);

		// new pages would not show up in the list
		return count($statuses) === 1 && $this->status() === $statuses[0];
	}

	/**
	 * The blueprint for the given name or null, if the
	 * blueprint cannot be loaded or is invalid
	 */
	protected function blueprint(string $name): PageBlueprint|null
	{
		try {
			return PageBlueprint::factory($this->model(), 'pages/' . $name);
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Lists `only` pages with these blueprints (or with any
	 * for `null`) and none with the blueprints in `except`
	 *
	 * @return array{only: list<string>|null, except: list<string>}
	 */
	protected function blueprints(): array
	{
		$blueprints = $this->blueprints;

		// a mapping only excludes blueprints via `except`
		$only = match (true) {
			$blueprints === null,
			$blueprints === [],
			is_array($blueprints) === true && array_is_list($blueprints) === false => null,
			default => A::wrap($blueprints)
		};

		return [
			'only'   => $only,
			'except' => [
				...A::wrap($blueprints['except'] ?? null),
				...A::wrap($this->templatesIgnore)
			]
		];
	}

	public function collector(): PagesCollector
	{
		return $this->collector ??= new PagesCollector(
			blueprints: $this->blueprints(),
			flip:       $this->flip(),
			limit:      $this->limit(),
			page:       (int)$this->page() ?: 1,
			parent:     $this->parentModel(),
			query:      $this->query(),
			search:     $this->searchterm(),
			sortBy:     $this->sortBy(),
			status:     $this->status(),
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
					blueprints: $this->acceptedPageBlueprints()
				)
			]
		];
	}
	// @codeCoverageIgnoreEnd

	/**
	 * Whether the list shows child pages of the field's
	 * model, optionally only with the given blueprint
	 */
	public function hasPageBlueprint(string|null $blueprint = null): bool
	{
		try {
			$parent = $this->parentModel();
		} catch (InvalidArgumentException) {
			return false;
		}

		// the list shows the children of another model
		if ($parent->is($this->model()) === false) {
			return false;
		}

		if ($blueprint === null) {
			return true;
		}

		$list = $this->blueprints();

		return
			($list['only'] === null || in_array($blueprint, $list['only'], true) === true) &&
			in_array($blueprint, $list['except'], true) === false;
	}

	/**
	 * @throws InvalidArgumentException If the parent is neither the site nor a page
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

	/**
	 * @return 'all'|'draft'|'listed'|'published'|'unlisted'
	 */
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
