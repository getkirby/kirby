<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class PageSettingsDropdownController extends ModelSettingsDropdownController
{
	/**
	 * @param \Kirby\Cms\Page $model
	 */
	public function __construct(
		protected ModelWithContent $model
	) {
		parent::__construct();
		$this->context     = $this->request->get(['view', 'sort', 'delete']);
		$this->permissions = $this->model->panel()->options(['preview']);
	}

	public static function factory(string $path): static
	{
		return new static(model: Find::page($path));
	}

	/**
	 * Provides options for the page dropdown
	 */
	public function load(): array
	{
		$url     = $this->model->panel()->url(true);
		$options = [];

		if ($this->view() === 'list') {
			$options['open'] = [
				'link'     => $this->model->previewUrl(),
				'target'   => '_blank',
				'icon'     => 'open',
				'text'     => $this->i18n('open'),
				'disabled' => $isPreviewDisabled = $this->isDisabledOption('preview')
			];

			$options['preview'] = [
				'icon'     => 'window',
				'link'     => $url . '/preview/compare',
				'text'     => $this->i18n('preview'),
				'disabled' => $isPreviewDisabled
			];

			$options[] = '-';
		}

		$options['changeTitle'] = [
			'dialog' => [
				'url'   => $url . '/changeTitle',
				'query' => ['select' => 'title']
			],
			'icon'     => 'title',
			'text'     => $this->i18n('rename'),
			'disabled' => $this->isDisabledOption('changeTitle')
		];

		$options['changeSlug'] = [
			'dialog' => [
				'url'   => $url . '/changeTitle',
				'query' => ['select' => 'slug']
			],
			'icon'     => 'url',
			'text'     => $this->i18n('page.changeSlug'),
			'disabled' => $this->isDisabledOption('changeSlug')
		];

		$options['changeStatus'] = [
			'dialog'   => $url . '/changeStatus',
			'icon'     => 'preview',
			'text'     => $this->i18n('page.changeStatus'),
			'disabled' => $this->isDisabledOption('changeStatus')
		];

		$siblings = $this->model->parentModel()->children()->listed()->not($this->model);

		$options['changeSort'] = [
			'dialog'   => $url . '/changeSort',
			'icon'     => 'sort',
			'text'     => $this->i18n('page.sort'),
			'disabled' => $siblings->count() === 0 || $this->isDisabledOption('sort')
		];

		$options['changeTemplate'] = [
			'dialog'   => $url . '/changeTemplate',
			'icon'     => 'template',
			'text'     => $this->i18n('page.changeTemplate'),
			'disabled' => $this->isDisabledOption('changeTemplate')
		];

		$options[] = '-';

		$options['move'] = [
			'dialog'   => $url . '/move',
			'icon'     => 'parent',
			'text'     => $this->i18n('page.move'),
			'disabled' => $this->isDisabledOption('move')
		];

		$options['duplicate'] = [
			'dialog'   => $url . '/duplicate',
			'icon'     => 'copy',
			'text'     => $this->i18n('duplicate'),
			'disabled' => $this->isDisabledOption('duplicate')
		];

		$options[] = '-';

		$options['delete'] = [
			'dialog'   => $url . '/delete',
			'icon'     => 'trash',
			'text'     => $this->i18n('delete'),
			'disabled' => $this->isDisabledOption('delete')
		];

		return $options;
	}
}
