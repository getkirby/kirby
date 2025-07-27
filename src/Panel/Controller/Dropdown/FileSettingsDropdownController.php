<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FileSettingsDropdownController extends ModelSettingsDropdownController
{
	/**
	 * @param \Kirby\Cms\File $model
	 */
	public function __construct(
		protected ModelWithContent $model
	) {
		parent::__construct($model);
		$this->context     = $this->request->get(['view', 'sort', 'delete']);
		$this->permissions = $this->model->panel()->options(['preview']);
	}

	public static function factory(string $parent, string $filename): static
	{
		return new static(model: Find::file($parent, $filename));
	}

	/**
	 * Provides options for the page dropdown
	 */
	public function load(): array
	{
		$url     = $this->model->panel()->url(true);
		$options = [];

		if ($this->view() === 'list') {
			$options[] = [
				'link'   => $this->model->previewUrl(),
				'target' => '_blank',
				'icon'   => 'open',
				'text'   => I18n::translate('open')
			];
			$options[] = '-';
		}

		$options[] = [
			'dialog'   => $url . '/changeName',
			'icon'     => 'title',
			'text'     => I18n::translate('rename'),
			'disabled' => $this->isDisabledOption('changeName')
		];

		if ($this->view() === 'list') {
			$options[] = [
				'dialog'   => $url . '/changeSort',
				'icon'     => 'sort',
				'text'     => I18n::translate('file.sort'),
				'disabled' => $this->isDisabledOption('sort')
			];
		}

		$options[] = [
			'dialog'   => $url . '/changeTemplate',
			'icon'     => 'template',
			'text'     => I18n::translate('file.changeTemplate'),
			'disabled' => $this->isDisabledOption('changeTemplate')
		];

		$options[] = '-';

		$options[] = [
			'click'    => 'replace',
			'icon'     => 'upload',
			'text'     => I18n::translate('replace'),
			'disabled' => $this->isDisabledOption('replace')
		];

		$options[] = '-';
		$options[] = [
			'dialog'   => $url . '/delete',
			'icon'     => 'trash',
			'text'     => I18n::translate('delete'),
			'disabled' => $this->isDisabledOption('delete')
		];

		return $options;
	}
}
