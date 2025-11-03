<?php

namespace Kirby\Panel;

use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Filesystem\Asset;
use Kirby\Panel\Ui\Buttons\ViewButtons;
use Kirby\Panel\Ui\FilePreview;
use Kirby\Panel\Ui\Item\FileItem;
use Kirby\Toolkit\I18n;
use Throwable;

/**
 * Provides information about the file model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class File extends Model
{
	/**
	 * @var \Kirby\Cms\File
	 */
	protected ModelWithContent $model;

	/**
	 * Breadcrumb array
	 */
	public function breadcrumb(): array
	{
		$breadcrumb = [];
		$parent     = $this->model->parent();

		switch ($parent::CLASS_ALIAS) {
			case 'user':
				/** @var \Kirby\Cms\User $parent */
				// The breadcrumb is not necessary
				// on the account view
				if ($parent->isLoggedIn() === false) {
					$breadcrumb[] = [
						'label' => $parent->username(),
						'link'  => $parent->panel()->url(true)
					];
				}
				break;
			case 'page':
				/** @var \Kirby\Cms\Page $parent */
				$breadcrumb = $this->model->parents()->flip()->values(
					fn ($parent) => [
						'label' => $parent->title()->toString(),
						'link'  => $parent->panel()->url(true),
					]
				);
		}

		// add the file
		$breadcrumb[] = [
			'label' => $this->model->filename(),
			'link'  => $this->url(true),
		];

		return $breadcrumb;
	}

	/**
	 * Returns header button names which should be displayed
	 * on the file view
	 */
	public function buttons(): array
	{
		return ViewButtons::view($this)->defaults(
			'open',
			'settings',
			'languages'
		)->render();
	}

	/**
	 * Provides a kirbytag or markdown
	 * tag for the file, which will be
	 * used in the panel, when the file
	 * gets dragged onto a textarea
	 *
	 * @param string|null $type (`auto`|`kirbytext`|`markdown`)
	 */
	public function dragText(
		string|null $type = 'auto',
		bool $absolute = false
	): string {
		$type = $this->dragTextType($type);
		$file = $this->model->type();
		$url  = match ($type) {
			'markdown' => $this->model->permalink(),
			default    => $this->model->uuid()
		};

		// if UUIDs are disabled, fall back to the filename
		// as relative link or the full absolute URL
		$url ??= match ($absolute) {
			false   => $this->model->filename(),
			default => $this->model->url()
		};


		if ($callback = $this->dragTextFromCallback($type, $url)) {
			return $callback;
		}

		if ($type === 'markdown') {
			return match ($file) {
				'image' => '![' . $this->model->alt() . '](' . $url . ')',
				default => '[' . $this->model->filename() . '](' . $url . ')'
			};
		}

		return match ($file) {
			'image', 'video' => '(' . $file . ': ' . $url . ')',
			default 		 => '(file: ' . $url . ')'
		};
	}

	/**
	 * Provides options for the file dropdown
	 */
	public function dropdown(array $options = []): array
	{
		$file     = $this->model;
		$request  = $file->kirby()->request();
		$defaults = $request->get(['delete', 'sort', 'view']);
		$options  = [...$defaults, ...$options];

		$permissions = $this->options(['preview']);
		$view        = $options['view'] ?? 'view';
		$url         = $this->url(true);
		$result      = [];

		if ($view === 'list') {
			$result[] = [
				'link'   => $file->previewUrl(),
				'target' => '_blank',
				'icon'   => 'open',
				'text'   => I18n::translate('open')
			];
			$result[] = '-';
		}

		$result[] = [
			'dialog'   => $url . '/changeName',
			'icon'     => 'title',
			'text'     => I18n::translate('rename'),
			'disabled' => $this->isDisabledDropdownOption('changeName', $options, $permissions)
		];

		if ($view === 'list') {
			$result[] = [
				'dialog'   => $url . '/changeSort',
				'icon'     => 'sort',
				'text'     => I18n::translate('file.sort'),
				'disabled' => $this->isDisabledDropdownOption('sort', $options, $permissions)
			];
		}

		$result[] = [
			'dialog'   => $url . '/changeTemplate',
			'icon'     => 'template',
			'text'     => I18n::translate('file.changeTemplate'),
			'disabled' => $this->isDisabledDropdownOption('changeTemplate', $options, $permissions)
		];

		$result[] = '-';

		$result[] = [
			'click'    => 'replace',
			'icon'     => 'upload',
			'text'     => I18n::translate('replace'),
			'disabled' => $this->isDisabledDropdownOption('replace', $options, $permissions)
		];

		$result[] = '-';
		$result[] = [
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
	 * for example
	 *
	 * @deprecated 5.1.4 Use the Kirby\Panel\Ui\Item\FileItem class instead
	 */
	public function dropdownOption(): array
	{
		return (new FileItem(file: $this->model))->props() + [
			'icon' => 'image'
		];
	}

	/**
	 * Returns the Panel icon color
	 */
	protected function imageColor(): string
	{
		$types = [
			'archive'  => 'gray-500',
			'audio'    => 'aqua-500',
			'code'     => 'pink-500',
			'document' => 'red-500',
			'image'    => 'orange-500',
			'video'    => 'yellow-500',
		];

		$extensions = [
			'csv'   => 'green-500',
			'doc'   => 'blue-500',
			'docx'  => 'blue-500',
			'indd'  => 'purple-500',
			'rtf'   => 'blue-500',
			'xls'   => 'green-500',
			'xlsx'  => 'green-500',
		];

		return
			$extensions[$this->model->extension()] ??
			$types[$this->model->type()] ??
			parent::imageDefaults()['color'];
	}

	/**
	 * Default settings for the file's Panel image
	 */
	protected function imageDefaults(): array
	{
		return [
			...parent::imageDefaults(),
			'color' => $this->imageColor(),
			'icon'  => $this->imageIcon(),
		];
	}

	/**
	 * Returns the Panel icon type
	 */
	protected function imageIcon(): string
	{
		$types = [
			'archive'  => 'archive',
			'audio'    => 'audio',
			'code'     => 'code',
			'document' => 'document',
			'image'    => 'image',
			'video'    => 'video',
		];

		$extensions = [
			'csv'   => 'table',
			'doc'   => 'pen',
			'docx'  => 'pen',
			'md'    => 'markdown',
			'mdown' => 'markdown',
			'rtf'   => 'pen',
			'xls'   => 'table',
			'xlsx'  => 'table',
		];

		return
			$extensions[$this->model->extension()] ??
			$types[$this->model->type()] ??
			'file';
	}

	/**
	 * Returns the image file object based on provided query
	 */
	protected function imageSource(
		string|null $query = null
	): CmsFile|Asset|null {
		if ($query === null && $this->model->isViewable()) {
			return $this->model;
		}

		return parent::imageSource($query);
	}

	/**
	 * Whether focus can be added in Panel view
	 */
	public function isFocusable(): bool
	{
		// blueprint option
		$option   = $this->model->blueprint()->focus();
		// fallback to whether the file is viewable
		// (images should be focusable by default, others not)
		$option ??= $this->model->isViewable();

		if ($option === false) {
			return false;
		}

		// ensure that user can update content file
		if ($this->options()['update'] === false) {
			return false;
		}

		$kirby = $this->model->kirby();

		// ensure focus is only added when editing primary/only language
		if (
			$kirby->multilang() === false ||
			$kirby->languages()->count() === 0 ||
			$kirby->language()->isDefault() === true
		) {
			return true;
		}

		return false;
	}

	/**
	 * Returns an array of all actions
	 * that can be performed in the Panel
	 *
	 * @param array $unlock An array of options that will be force-unlocked
	 */
	public function options(array $unlock = []): array
	{
		$options = parent::options($unlock);

		try {
			// check if the file type is allowed at all,
			// otherwise it cannot be replaced
			$this->model->match($this->model->blueprint()->accept());
		} catch (Throwable) {
			$options['replace'] = false;
		}

		return $options;
	}

	/**
	 * Returns the full path without leading slash
	 */
	public function path(): string
	{
		return 'files/' . $this->model->filename();
	}

	/**
	 * Prepares the response data for file pickers
	 * and file fields
	 */
	public function pickerData(array $params = []): array
	{
		$name     = $this->model->filename();
		$id       = $this->model->id();
		$absolute = false;

		if (empty($params['model']) === false) {
			$parent   = $this->model->parent();
			$absolute = $parent !== $params['model'];

			// if the file belongs to the current parent model,
			// store only name as ID to keep its path relative to the model
			$id = match ($absolute) {
				true  => $id,
				false => $name
			};
		}

		$item = new FileItem(
			file: $this->model,
			dragTextIsAbsolute: $absolute,
			image: $params['image'] ?? null,
			info: $params['info'] ?? null,
			layout: $params['layout'] ?? null,
			text: $params['text'] ?? null,
		);

		return [
			...$item->props(),
			'id'       => $id,
			'sortable' => true,
			'type'     => $this->model->type(),
		];
	}

	/**
	 * Returns the data array for the view's component props
	 */
	public function props(): array
	{
		$props = parent::props();
		$file  = $this->model;

		// Additional model information
		// @deprecated Use the top-level props instead
		$model = [
			'dimensions' => $file->dimensions()->toArray(),
			'extension'  => $file->extension(),
			'filename'   => $file->filename(),
			'link'       => $props['link'],
			'mime'       => $file->mime(),
			'niceSize'   => $file->niceSize(),
			'id'         => $props['id'],
			'parent'     => $file->parent()->panel()->path(),
			'template'   => $file->template(),
			'type'       => $file->type(),
			'url'        => $file->url(),
			'uuid'       => $props['uuid'],
		];

		return [
			...$props,
			...$this->prevNext(),
			'blueprint' => $this->model->template() ?? 'default',
			'extension' => $model['extension'],
			'filename'  => $model['filename'],
			'mime'      => $model['mime'],
			'model'     => $model,
			'preview'   => FilePreview::factory($this->model)->render(),
			'type'      => $model['type'],
			'url'       => $model['url'],
		];
	}

	/**
	 * Returns navigation array with previous and next file
	 */
	public function prevNext(): array
	{
		$file     = $this->model;
		$siblings = $file->templateSiblings()->sortBy(
			'sort',
			'asc',
			'filename',
			'asc'
		);

		return [
			'next' => function () use ($file, $siblings): array|null {
				$next = $siblings->nth($siblings->indexOf($file) + 1);
				return $this->toPrevNextLink($next, 'filename');
			},
			'prev' => function () use ($file, $siblings): array|null {
				$prev = $siblings->nth($siblings->indexOf($file) - 1);
				return $this->toPrevNextLink($prev, 'filename');
			}
		];
	}
	/**
	 * Returns the url to the editing view
	 * in the panel
	 */
	public function url(bool $relative = false): string
	{
		$parent = $this->model->parent()->panel()->url($relative);
		return $parent . '/' . $this->path();
	}

	/**
	 * Returns the data array for this model's Panel view
	 */
	public function view(): array
	{
		return [
			'breadcrumb' => fn (): array => $this->model->panel()->breadcrumb(),
			'component'  => 'k-file-view',
			'props'      => $this->props(),
			'search'     => 'files',
			'title'      => $this->model->filename(),
		];
	}
}
