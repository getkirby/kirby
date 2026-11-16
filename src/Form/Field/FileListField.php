<?php

namespace Kirby\Form\Field;

use Kirby\Api\Api;
use Kirby\Blueprint\FileBlueprint;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Form\Interface\ProvidesAcceptedFileBlueprints;
use Kirby\Panel\Collector\FilesCollector;
use Kirby\Panel\Ui\Item\FileItem;
use Kirby\Panel\Ui\Upload;
use Kirby\Reflection\Attributes\Prop;

/**
 * Lists the files of a model
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelListField<File>
 */
class FileListField extends ModelListField implements ProvidesAcceptedFileBlueprints
{
	public const string TYPE = 'files';

	#[Prop(export: false)]
	protected array|string|null $text = '{{ file.filename }}';

	/**
	 * Option to switch off the upload button
	 */
	#[Prop(export: false)]
	protected bool $create = true;

	/**
	 * Filters the files by blueprint and sets the blueprint for all uploads
	 */
	#[Prop(aliases: ['template'], export: false)]
	protected string|null $blueprint = null;

	public function accept(): string|null
	{
		if ($this->blueprint === null) {
			return null;
		}

		return FileBlueprint::factory(
			$this->model(),
			'files/' . $this->blueprint,
			'files/default'
		)?->acceptAttribute();
	}

	/**
	 * Accepts files with its own blueprint
	 * or with any blueprint if none is set
	 *
	 * @return list<string>
	 */
	public function acceptedFileBlueprints(): array
	{
		$blueprint = $this->blueprint();

		if ($blueprint !== null && $blueprint !== '') {
			return [$blueprint];
		}

		return $this->kirby()->blueprints('files');
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

					/** @var File|Page|Site|User $parent */
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

	public function blueprint(): string|null
	{
		return $this->blueprint;
	}

	public function collector(): FilesCollector
	{
		return $this->collector ??= new FilesCollector(
			blueprint: $this->blueprint(),
			flip:      $this->flip(),
			limit:     $this->limit(),
			page:      (int)$this->page() ?: 1,
			parent:    $this->parentModel(),
			query:     $this->query(),
			search:    $this->searchterm(),
			sortBy:    $this->sortBy(),
		);
	}

	public function create(): bool
	{
		return $this->create;
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
	 *
	 * @return array<string, mixed>|false
	 */
	public function upload(): array|false
	{
		if ($this->create() === false || $this->isFull() === true) {
			return false;
		}

		$total    = $this->total();
		$settings = new Upload(
			api:       $this->parentModel()->apiUrl(true) . '/files',
			accept:    $this->accept(),
			blueprint: $this->blueprint(),
			max:       $this->max() ? $this->max() - $total : null,
			preview:   $this->image(),
			sort:      $this->sortable() === true ? $total + 1 : null,
		);

		return $settings->props();
	}
}
