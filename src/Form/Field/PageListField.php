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
	 * Templates that may be added or `false` to disable page creation
	 */
	#[Prop(export: false)]
	protected array|string|bool|null $create = null;

	/**
	 * Filters the pages by their status
	 */
	#[Prop(export: false)]
	protected string $status = 'all';

	/**
	 * Filters the list by a single template or a list of templates
	 */
	#[Prop(export: false)]
	protected string|array|null $template = null;

	/**
	 * Filters the list by templates and sets the template
	 * options for newly created pages
	 */
	#[Prop(computed: true, export: false)]
	protected array|string|null $templates = null;

	/**
	 * Excludes the given templates from the list
	 */
	#[Prop(export: false)]
	protected array|string $templatesIgnore = [];

	/**
	 * Accepts pages with the blueprints from `create`,
	 * the list's templates or any blueprint otherwise
	 *
	 * @return list<string>
	 */
	public function acceptedPageBlueprints(): array
	{
		// no pages can be added through the list
		if ($this->create === false) {
			return [];
		}

		$blueprints = match ($this->create) {
			null, true, '', [] => $this->templates(),
			default            => A::wrap($this->create)
		};

		if ($blueprints === []) {
			$blueprints = $this->kirby()->blueprints();
		}

		return array_values(array_diff($blueprints, $this->templatesIgnore()));
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

	public function collector(): PagesCollector
	{
		return $this->collector ??= new PagesCollector(
			flip:            $this->flip(),
			limit:           $this->limit(),
			page:            (int)$this->page() ?: 1,
			parent:          $this->parentModel(),
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

		$templates = $this->templates();

		return
			($templates === [] || in_array($blueprint, $templates, true) === true) &&
			in_array($blueprint, $this->templatesIgnore(), true) === false;
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
