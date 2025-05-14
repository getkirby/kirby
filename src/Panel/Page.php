<?php

namespace Kirby\Panel;

use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Filesystem\Asset;
use Kirby\Panel\Ui\Buttons\ViewButtons;
use Kirby\Toolkit\I18n;

/**
 * Provides information about the page model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Page extends Model
{
	/**
	 * @var \Kirby\Cms\Page
	 */
	protected ModelWithContent $model;

	/**
	 * Breadcrumb array
	 */
	public function breadcrumb(): array
	{
		$parents = $this->model->parents()->flip()->merge($this->model);

		return $parents->values(
			fn ($parent) => [
				'label' => $parent->title()->toString(),
				'link'  => $parent->panel()->url(true),
			]
		);
	}

	/**
	 * Returns header buttons which should be displayed
	 * on the page view
	 */
	public function buttons(): array
	{
		return ViewButtons::view($this)->defaults(
			'open',
			'preview',
			'settings',
			'languages',
			'status'
		)->render();
	}

	/**
	 * Provides a kirbytag or markdown
	 * tag for the page, which will be
	 * used in the panel, when the page
	 * gets dragged onto a textarea
	 *
	 * @internal
	 * @param string|null $type (`auto`|`kirbytext`|`markdown`)
	 */
	public function dragText(string|null $type = null): string
	{
		$type = $this->dragTextType($type);

		if ($callback = $this->dragTextFromCallback($type)) {
			return $callback;
		}

		$title = $this->model->title();

		// type: markdown
		if ($type === 'markdown') {
			$url = $this->model->permalink() ?? $this->model->url();
			return '[' . $title . '](' . $url . ')';
		}

		// type: kirbytext
		$link = $this->model->uuid() ?? $this->model->uri();
		return '(link: ' . $link . ' text: ' . $title . ')';
	}

	/**
	 * Provides options for the page dropdown
	 */
	public function dropdown(array $options = []): array
	{
		$page        = $this->model;
		$request     = $page->kirby()->request();
		$defaults    = $request->get(['view', 'sort', 'delete']);
		$options     = [...$defaults, ...$options];
		$permissions = $this->options(['preview']);
		$view        = $options['view'] ?? 'view';
		$url         = $this->url(true);
		$result      = [];

		if ($view === 'list') {
			$result['open'] = [
				'link'     => $page->previewUrl(),
				'target'   => '_blank',
				'icon'     => 'open',
				'text'     => I18n::translate('open'),
				'disabled' => $isPreviewDisabled = $this->isDisabledDropdownOption('preview', $options, $permissions)
			];

			$result['preview'] = [
				'icon'     => 'window',
				'link'     => $page->panel()->url(true) . '/preview/changes',
				'text'     => I18n::translate('preview'),
				'disabled' => $isPreviewDisabled
			];

			$result[] = '-';
		}

		$result['changeTitle'] = [
			'dialog' => [
				'url'   => $url . '/changeTitle',
				'query' => [
					'select' => 'title'
				]
			],
			'icon'     => 'title',
			'text'     => I18n::translate('rename'),
			'disabled' => $this->isDisabledDropdownOption('changeTitle', $options, $permissions)
		];

		$result['changeSlug'] = [
			'dialog' => [
				'url'   => $url . '/changeTitle',
				'query' => [
					'select' => 'slug'
				]
			],
			'icon'     => 'url',
			'text'     => I18n::translate('page.changeSlug'),
			'disabled' => $this->isDisabledDropdownOption('changeSlug', $options, $permissions)
		];

		$result['changeStatus'] = [
			'dialog'   => $url . '/changeStatus',
			'icon'     => 'preview',
			'text'     => I18n::translate('page.changeStatus'),
			'disabled' => $this->isDisabledDropdownOption('changeStatus', $options, $permissions)
		];

		$siblings = $page->parentModel()->children()->listed()->not($page);

		$result['changeSort'] = [
			'dialog'   => $url . '/changeSort',
			'icon'     => 'sort',
			'text'     => I18n::translate('page.sort'),
			'disabled' => $siblings->count() === 0 || $this->isDisabledDropdownOption('sort', $options, $permissions)
		];

		$result['changeTemplate'] = [
			'dialog'   => $url . '/changeTemplate',
			'icon'     => 'template',
			'text'     => I18n::translate('page.changeTemplate'),
			'disabled' => $this->isDisabledDropdownOption('changeTemplate', $options, $permissions)
		];

		$result[] = '-';

		$result['move'] = [
			'dialog'   => $url . '/move',
			'icon'     => 'parent',
			'text'     => I18n::translate('page.move'),
			'disabled' => $this->isDisabledDropdownOption('move', $options, $permissions)
		];

		$result['duplicate'] = [
			'dialog'   => $url . '/duplicate',
			'icon'     => 'copy',
			'text'     => I18n::translate('duplicate'),
			'disabled' => $this->isDisabledDropdownOption('duplicate', $options, $permissions)
		];

		$result[] = '-';

		$result['delete'] = [
			'dialog'   => $url . '/delete',
			'icon'     => 'trash',
			'text'     => I18n::translate('delete'),
			'disabled' => $this->isDisabledDropdownOption('delete', $options, $permissions)
		];

		return $result;
	}

	/**
	 * Returns the setup for a dropdown option
	 * which is used in the changes dropdown
	 * for example.
	 */
	public function dropdownOption(): array
	{
		return [
			'text' => $this->model->title()->value(),
		] + parent::dropdownOption();
	}

	/**
	 * Returns the escaped Id, which is
	 * used in the panel to make routing work properly
	 */
	public function id(): string
	{
		return str_replace('/', '+', $this->model->id());
	}

	/**
	 * Default settings for the page's Panel image
	 */
	protected function imageDefaults(): array
	{
		$defaults = [];

		if ($icon = $this->model->blueprint()->icon()) {
			$defaults['icon'] = $icon;
		}

		return [
			...parent::imageDefaults(),
			...$defaults
		];
	}

	/**
	 * Returns the image file object based on provided query
	 *
	 * @internal
	 */
	protected function imageSource(
		string|null $query = null
	): CmsFile|Asset|null {
		$query ??= 'page.image';
		return parent::imageSource($query);
	}

	/**
	 * Returns the full path without leading slash
	 *
	 * @internal
	 */
	public function path(): string
	{
		return 'pages/' . $this->id();
	}

	/**
	 * Prepares the response data for page pickers
	 * and page fields
	 */
	public function pickerData(array $params = []): array
	{
		$params['text'] ??= '{{ page.title }}';

		return [
			...parent::pickerData($params),
			'dragText'    => $this->dragText(),
			'hasChildren' => $this->model->hasChildren(),
			'url'         => $this->model->url()
		];
	}

	/**
	 * The best applicable position for
	 * the position/status dialog
	 */
	public function position(): int
	{
		return
			$this->model->num() ??
			$this->model->parentModel()->children()->listed()->not($this->model)->count() + 1;
	}

	/**
	 * Returns navigation array with
	 * previous and next page
	 * based on blueprint definition
	 *
	 * @internal
	 */
	public function prevNext(): array
	{
		$page = $this->model;

		// create siblings collection based on
		// blueprint navigation
		$siblings = static function (string $direction) use ($page) {
			$navigation = $page->blueprint()->navigation();
			$sortBy     = $navigation['sortBy'] ?? null;
			$status     = $navigation['status'] ?? null;
			$template   = $navigation['template'] ?? null;
			$direction  = $direction === 'prev' ? 'prev' : 'next';

			// if status is defined in navigation,
			// all items in the collection are used
			// (drafts, listed and unlisted) otherwise
			// it depends on the status of the page
			$siblings = $status !== null ? $page->parentModel()->childrenAndDrafts() : $page->siblings();

			// sort the collection if custom sortBy
			// defined in navigation otherwise
			// default sorting will apply
			if ($sortBy !== null) {
				$siblings = $siblings->sort(...$siblings::sortArgs($sortBy));
			}

			$siblings = $page->{$direction . 'All'}($siblings);

			if (empty($navigation) === false) {
				$statuses  = (array)($status ?? $page->status());
				$templates = (array)($template ?? $page->intendedTemplate());

				// do not filter if template navigation is all
				if (in_array('all', $templates, true) === false) {
					$siblings = $siblings->filter('intendedTemplate', 'in', $templates);
				}

				// do not filter if status navigation is all
				if (in_array('all', $statuses, true) === false) {
					$siblings = $siblings->filter('status', 'in', $statuses);
				}
			} else {
				$siblings = $siblings
					->filter('intendedTemplate', $page->intendedTemplate())
					->filter('status', $page->status());
			}

			return $siblings->filter('isListable', true);
		};

		return [
			'next' => fn () => $this->toPrevNextLink($siblings('next')->first()),
			'prev' => fn () => $this->toPrevNextLink($siblings('prev')->last())
		];
	}

	/**
	 * Returns the data array for the
	 * view's component props
	 *
	 * @internal
	 */
	public function props(): array
	{
		$props = parent::props();

		// Additional model information
		// @deprecated Use the top-level props instead
		$model = [
			'id'         => $props['id'],
			'link'       => $props['link'],
			'parent'     => $this->model->parentModel()->panel()->url(true),
			'previewUrl' => $this->model->previewUrl(),
			'status'     => $this->model->status(),
			'title'      => $this->model->title()->toString(),
			'uuid'       => $props['uuid'],
		];

		return [
			...$props,
			...$this->prevNext(),
			'blueprint'  => $this->model->intendedTemplate()->name(),
			'model'      => $model,
			'title'      => $model['title'],
		];
	}

	/**
	 * Returns the data array for
	 * this model's Panel view
	 *
	 * @internal
	 */
	public function view(): array
	{
		return [
			'breadcrumb' => $this->model->panel()->breadcrumb(),
			'component'  => 'k-page-view',
			'props'      => $props = $this->props(),
			'title'      => $props['title'],
		];
	}
}
