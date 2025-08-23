<?php

namespace Kirby\Panel;

use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Filesystem\Asset;
use Kirby\Panel\Controller\Dropdown\PageSettingsDropdownController;
use Kirby\Panel\Controller\View\PageViewController;
use Kirby\Panel\Ui\Item\PageItem;
use Override;

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
	 * Provides a kirbytag or markdown
	 * tag for the page, which will be
	 * used in the panel, when the page
	 * gets dragged onto a textarea
	 *
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
	 * @deprecated 6.0.0 Use `Kirby\Panel\Controller\Dropdown\PageSettingsDropdownController` instead
	 */
	public function dropdown(): array
	{
		return (new PageSettingsDropdownController($this->model))->load();
	}

	/**
	 * Returns the setup for a dropdown option
	 * which is used in the changes dropdown
	 * for example.
	 */
	#[Override]
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
	#[Override]
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
	 */
	#[Override]
	protected function imageSource(
		string|null $query = null
	): CmsFile|Asset|null {
		$query ??= 'page.image';
		return parent::imageSource($query);
	}

	/**
	 * Returns the full path without leading slash
	 */
	#[Override]
	public function path(): string
	{
		return 'pages/' . $this->id();
	}

	/**
	 * Prepares the response data for page pickers
	 * and page fields
	 */
	#[Override]
	public function pickerData(array $params = []): array
	{
		$item = new PageItem(
			page: $this->model,
			image: $params['image'] ?? null,
			info: $params['info'] ?? null,
			layout: $params['layout'] ?? null,
			text: $params['text'] ?? null,
		);

		return [
			...$item->props(),
			'hasChildren' => $this->model->hasChildren(),
			'sortable'    => true
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
	 * @codeCoverageIgnore
	 */
	#[Override]
	protected function viewController(): PageViewController
	{
		return new PageViewController($this->model);
	}
}
