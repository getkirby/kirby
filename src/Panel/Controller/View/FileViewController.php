<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Model;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\FilePreview;

/**
 * Controls a file view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FileViewController extends ModelViewController
{
	/**
	 * @var \Kirby\Cms\File $model
	 */
	protected ModelWithContent $model;

	/**
	 * @var \Kirby\Panel\File
	 */
	protected Model $panel;

	protected Files $siblings;

	public function __construct(
		File $model
	) {
		parent::__construct($model);
	}

	public function breadcrumb(): array
	{
		return [
			[
				'label' => $this->model->filename(),
				'link'  => $this->panel->url(true),
			]
		];
	}

	public function buttons(): ViewButtons
	{
		return parent::buttons()->defaults(
			'open',
			'settings',
			'languages'
		);
	}

	public static function factory(string $parent, string $filename): static
	{
		return new static(model: Find::file($parent, $filename));
	}

	public function index(): int|false
	{
		return $this->siblings()->indexOf($this->model);
	}

	public function next(): array|null
	{
		return static::prevNext(
			model:  $this->siblings()->nth($this->index() + 1),
			title: 'filename'
		);
	}

	public function prev(): array|null
	{
		return static::prevNext(
			model:  $this->siblings()->nth($this->index() - 1),
			title: 'filename'
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'blueprint' => $this->model->template() ?? 'default',
			'extension' => $this->model->extension(),
			'filename'  => $this->model->filename(),
			'mime'      => $this->model->mime(),
			'preview'   => FilePreview::factory($this->model)->render(...),
			'search'    => 'files',
			'type'      => $this->model->type(),
			'url'       => $this->model->url(),
		];
	}

	protected function siblings(): Files
	{
		return $this->siblings ??= $this->model->templateSiblings()->sortBy(
			'sort',
			'asc',
			'filename',
			'asc'
		);
	}

	public function title(): string
	{
		return $this->model->filename();
	}
}
