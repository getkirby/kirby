<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Collector\FilesCollector;
use Kirby\Panel\Ui\Item\FileItem;

/**
 * Controls the Panel dialog for selecting files
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FilePickerDialogController extends ModelPickerDialogController
{
	protected const string TYPE = 'file';

	protected FilesCollector $collector;

	public function __construct(
		ModelWithContent $model,
		bool $hasSearch = true,
		array|null $image = [],
		string|null $info = null,
		string $layout = 'list',
		public int|null $limit = null,
		int|null $max = null,
		bool $multiple = true,
		public string|null $query = null,
		string|null $size = null,
		string|null $text = null
	) {
		parent::__construct(
			model:     $model,
			hasSearch: $hasSearch,
			image:     $image,
			info:      $info,
			layout:    $layout,
			max:       $max,
			multiple:  $multiple,
			size:      $size,
			text:      $text,
		);
	}

	public function collector(): FilesCollector
	{
		return $this->collector ??= new FilesCollector(
			limit:  $this->limit,
			page:   $this->page,
			parent: $this->model,
			query:  $this->query(),
			search: $this->search,
		);
	}

	public function find(string $id): File|null
	{
		return $this->kirby->file($id, $this->model);
	}

	/**
	 * Returns the item data for a file
	 * @param \Kirby\Cms\File $model
	 */
	public function item(ModelWithContent $model): array
	{
		return (new FileItem(
			file:               $model,
			dragTextIsAbsolute: $model->parent()->is($this->model),
			image:              $this->image,
			info:               $this->info,
			layout:             $this->layout,
			text:               $this->text
		))->props();
	}

	public function query(): string
	{
		if ($this->query !== null) {
			return $this->query;
		}

		if ($this->model instanceof File) {
			return 'file.siblings';
		}

		return $this->model::CLASS_ALIAS . '.files';
	}
}
