<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Collector\FilesCollector;

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
class FilesPickerDialogController extends ModelsPickerDialogController
{
	protected const TYPE = 'files';

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
		string|null $text = null,
		public array|false $uploads = false,
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

	public function props(): array
	{
		return [
			...parent::props(),
			'uploads' => $this->uploads(),
		];
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

	public function uploads(): array|false
	{
		if ($this->uploads === false) {
			return false;
		}

		return [
			'multiple' => $this->multiple,
			'max'      => $this->max,
			...$this->uploads,
		];
	}
}
