<?php

namespace Kirby\Form\Field;

use Kirby\Api\Api;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Mixin;
use Kirby\Panel\Controller\Dialog\FilePickerDialogController;

/**
 * Textarea Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TextareaField extends InputField
{
	use Mixin\Counter;
	use Mixin\Font;
	use Mixin\Maxlength;
	use Mixin\Minlength;
	use Mixin\Spellcheck;
	use Mixin\Upload;

	/**
	 * Enables/disables the format buttons.
	 * Can either be `true`/`false` or a list of allowed buttons.
	 * Available buttons: `headlines`, `italic`, `bold`, `link`, `email`,
	 * `file`, `code`, `ul`, `ol` (as well as `|` for a divider)
	 */
	protected array|bool|null $buttons;

	/**
	 * Sets the options for the files picker
	 */
	protected mixed $files;

	/**
	 * Changes the size of the textarea.
	 * Available sizes: `small`, `medium`, `large`, `huge`
	 */
	protected string|null $size;

	protected string $value = '';

	public function __construct(
		array|bool|null $buttons = null,
		bool|null $counter = null,
		mixed $files = null,
		string|null $font = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		string|null $size = null,
		bool|null $spellcheck = null,
		mixed $uploads = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->buttons    = $buttons;
		$this->counter    = $counter;
		$this->files      = $files;
		$this->font       = $font;
		$this->maxlength  = $maxlength;
		$this->minlength  = $minlength;
		$this->size       = $size;
		$this->spellcheck = $spellcheck;
		$this->uploads    = $uploads;
	}

	public function api(): array
	{
		$field = $this;

		return [
			[
				'pattern' => 'upload',
				'method'  => 'POST',
				'action'  => function () use ($field) {
					// @codeCoverageIgnoreStart
					/**
					 * @var Api
					 */
					$api = $this;

					return $field->upload(
						$api,
						$field->uploads(),
						fn (File $file, ModelWithContent $parent) => [
							'filename' => $file->filename(),
							'dragText' => $file->panel()->dragText(
								absolute: $field->model()->is($parent) === false
							),
						]
					);
					// @codeCoverageIgnoreEnd
				}
			]
		];
	}

	public function buttons(): array|bool
	{
		return $this->buttons ?? true;
	}

	public function default(): string|null
	{
		$default = parent::default();
		return $default ? trim($default) : null;
	}

	public function dialogs(): array
	{
		$settings = match (true) {
			is_string($this->files) => ['query' => $this->files],
			is_array($this->files)  => $this->files,
			default                 => [],
		};

		return [
			'files' => fn () => new FilePickerDialogController(
				...$settings,
				model: $this->model(),
			)
		];
	}

	public function fill(mixed $value): static
	{
		return parent::fill(
			value: trim($value ?? '')
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'buttons'    => $this->buttons(),
			'counter'    => $this->counter(),
			'font'       => $this->font(),
			'maxlength'  => $this->maxlength(),
			'minlength'  => $this->minlength(),
			'spellcheck' => $this->spellcheck(),
			'size'       => $this->size(),
			'uploads'    => $this->uploads()
		];
	}

	public function size(): string|null
	{
		return $this->size;
	}

	protected function validations(): array
	{
		return [
			'minlength',
			'maxlength'
		];
	}
}
