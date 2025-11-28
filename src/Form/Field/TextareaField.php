<?php

namespace Kirby\Form\Field;

use Closure;
use Kirby\Api\Api;
use Kirby\Cms\File;
use Kirby\Cms\FilePicker;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Mixin;

/**
 * Textarea Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TextareaField extends StringField
{
	use Mixin\Icon;

	/**
	 * Enables/disables the format buttons. Can either be `true`/`false` or a list of allowed buttons. Available buttons: `headlines`, `italic`, `bold`, `link`, `email`, `file`, `code`, `ul`, `ol` (as well as `|` for a divider)
	 */
	protected array|bool|null $buttons;

	/**
	 * Sets the options for the files picker
	 */
	protected array|null $files;

	/**
	 * Changes the size of the textarea. Available sizes: `small`, `medium`, `large`, `huge`
	 */
	protected string|null $size;

	/**
	 * Sets the upload options for linked files (since 3.2.0)
	 */
	protected array|bool|string|null $uploads;

	public function __construct(
		string|null $autocomplete = null,
		bool|null $autofocus = null,
		array|bool|null $buttons = null,
		bool|null $counter = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|null $files = null,
		string|null $font = null,
		array|string|null $help = null,
		string|null $icon = null,
		array|string|null $label = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		string|null $name = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		string|null $size = null,
		bool|null $spellcheck = null,
		bool|null $translate = null,
		array|bool|string|null $uploads = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autocomplete: $autocomplete,
			autofocus: $autofocus,
			counter: $counter,
			default: $default,
			disabled: $disabled,
			font: $font,
			help: $help,
			label: $label,
			name: $name,
			maxlength: $maxlength,
			minlength: $minlength,
			placeholder: $placeholder,
			required: $required,
			spellcheck: $spellcheck,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->buttons = $buttons;
		$this->files   = $files;
		$this->icon    = $icon;
		$this->size    = $size;
		$this->uploads = $uploads;
	}

	public function buttons(): array|bool
	{
		return $this->buttons ?? true;
	}

	public function fill(mixed $value): static
	{
		$this->value = trim($value ?? '');
		return $this;
	}

	public function filepicker(int|null $page, string|null $search): array
	{
		return (new FilePicker([
			...$this->filepickerSettings(),
			'page'   => $page,
			'search' => $search
		]))->toArray();
	}

	protected function filepickerSettings(): array
	{
		$settings = match (true) {
			is_string($this->files) => ['query' => $this->files],
			is_array($this->files)  => $this->files,
			default                 => [],
		};

		return [
			...$settings,
			'model' => $this->model(),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'buttons' => $this->buttons(),
			'icon'    => $this->icon(),
			'size'    => $this->size(),
			'uploads' => $this->uploads(),
		];
	}

	public function routes(): array
	{
		$field = $this;
		return [
			[
				'pattern' => 'files',
				'action'  => function () use ($field) {
					/**
					 * @var \Kirby\Api\Api
					 */
					$api = $this;
					return $field->filepicker(
						page:   $api->requestQuery('page'),
						search: $api->requestQuery('search')
					);
				}
			],
			[
				'pattern' => 'upload',
				'method' => 'POST',
				'action' => function () use ($field) {
					/**
					 * @var \Kirby\Api\Api
					 */
					$api      = $this;
					$settings = $field->uploads();

					if ($settings === false) {
						throw new Exception(
							message: 'Uploads are disabled for this field'
						);
					}

					return $api->upload(function (string $source, string $filename) use ($field, $settings) {
						return $field->upload($source, $filename, $settings);
					});
				}
			]
		];
	}

	public function size(): string|null
	{
		return $this->size;
	}

	protected function upload(string $source, string $filename, array $settings): array
	{
		$parent = $this->uploadParent($settings['parent']);

		$props = [
			'source'   => $source,
			'template' => $settings['template'],
			'filename' => $filename,
		];

		// move the source file from the temp dir
		$file = $parent->createFile($props, true);

		if ($file instanceof File === false) {
			throw new Exception(
				message: 'The file could not be uploaded'
			);
		}

		return [
			'filename' => $file->filename(),
			'dragText' => $file->panel()->dragText(
				absolute: $this->model()->is($parent) === false
			),
		];
	}

	protected function uploadParent(string|null $parentQuery = null): Page|Site|User
	{
		$parent = $this->model();

		if ($parentQuery !== null) {
			$parent = $parent->query($parentQuery);
		}

		if ($parent instanceof File) {
			$parent = $parent->parent();
		}

		return $parent;
	}

	public function uploads(): array|false
	{
		$uploads = $this->uploads;

		if ($uploads === false) {
			return false;
		}

		$uploads = match (true) {
			is_string($uploads) => ['template' => $uploads],
			is_array($uploads)  => $uploads,
			default             => []
		};

		// add defaults
		$uploads = [
			'accept'   => '*',
			'parent'   => null,
			'template' => null,
			...$uploads
		];

		if ($template = $uploads['template']) {
			// get parent object for upload target
			$parent = $this->uploadParent($uploads['parent']);

			if ($parent === null) {
				throw new InvalidArgumentException(
					message: '"' . $uploads['parent'] . '" could not be resolved as a valid parent for the upload'
				);
			}

			$file = new File([
				'filename' => 'tmp',
				'parent'   => $parent,
				'template' => $template
			]);

			$uploads['accept'] = $file->blueprint()->acceptAttribute();
		}

		return $uploads;
	}

}
