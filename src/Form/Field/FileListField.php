<?php

namespace Kirby\Form\Field;

use Kirby\Api\Api;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Panel\Collector\FilesCollector;
use Kirby\Panel\Ui\Item\FileItem;
use Kirby\Panel\Ui\Upload;

/**
 * Lists the files of a model
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelListField<File>
 */
class FileListField extends ModelListField
{
	public const string TYPE = 'files';

	/**
	 * Option to switch off the upload button
	 */
	protected bool|null $create;

	/**
	 * Filters the files by template and sets the template for all uploads
	 */
	protected string|null $template;

	public function __construct(
		bool|null $batch = null,
		array|null $columns = null,
		bool|null $create = null,
		array|string|null $empty = null,
		bool|null $flip = null,
		array|string|null $help = null,
		array|false|null $image = null,
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
		string|null $template = null,
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

		$this->create   = $create;
		$this->template = $template;
	}

	public function accept(): string|null
	{
		if ($this->template === null) {
			return null;
		}

		$file = new File([
			'filename' => 'tmp',
			'parent'   => $this->model(),
			'template' => $this->template
		]);

		return $file->blueprint()->acceptAttribute();
	}

	public function api(): array
	{
		$field = $this;

		return [
			...parent::api(),
			[
				'pattern' => 'sort',
				'method'  => 'PATCH',
				// @codeCoverageIgnoreStart
				'action'  => function () use ($field): bool {
					/** @var Api $api */
					$api = $this;

					/** @var Page|Site|User $parent */
					$parent = $field->parentModel();

					$parent->files()->changeSort(
						$api->requestBody('files'),
						$api->requestBody('index')
					);

					return true;
				}
				// @codeCoverageIgnoreEnd
			]
		];
	}

	public function collector(): FilesCollector
	{
		return $this->collector ??= new FilesCollector(
			flip:     $this->flip(),
			limit:    $this->limit(),
			page:     (int)$this->page() ?: 1,
			parent:   $this->parentModel(),
			query:    $this->query(),
			search:   $this->searchterm(),
			sortBy:   $this->sortBy(),
			template: $this->template(),
		);
	}

	public function create(): bool
	{
		return $this->create ?? true;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'accept' => $this->accept()
		];
	}

	public function state(): array
	{
		return [
			...parent::state(),
			'upload' => $this->upload()
		];
	}

	public function template(): string|null
	{
		return $this->template;
	}

	public function text(): string
	{
		return parent::text() ?? '{{ file.filename }}';
	}

	/**
	 * @param File $model
	 */
	public function toItem(ModelWithContent $model): array
	{
		return (new FileItem(
			file:               $model,
			// the link is only set when the files come from another model
			dragTextIsAbsolute: $this->link() !== null,
			image:              $this->image(),
			info:               $this->info(),
			layout:             $this->layout(),
			text:               $this->text(),
		))->props();
	}

	/**
	 * Upload settings or `false` if uploads are disabled
	 */
	public function upload(): array|false
	{
		if ($this->create() === false || $this->isFull() === true) {
			return false;
		}

		$total    = $this->total();
		$settings = new Upload(
			api:      $this->parentModel()->apiUrl(true) . '/files',
			accept:   $this->accept(),
			max:      $this->max() ? $this->max() - $total : null,
			preview:  $this->image(),
			sort:     $this->sortable() === true ? $total + 1 : null,
			template: $this->template(),
		);

		return $settings->props();
	}
}
