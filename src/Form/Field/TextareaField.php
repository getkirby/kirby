<?php

namespace Kirby\Form\Field;

use Kirby\Api\Api;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Mixin;
use Kirby\Panel\Controller\Dialog\FilePickerDialogController;
use Kirby\Reflection\Attributes\Prop;

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
	#[Prop]
	protected array|bool $buttons = true;

	/**
	 * Sets the options for the files picker
	 */
	#[Prop(export: false)]
	protected mixed $files = null;

	/**
	 * Changes the size of the textarea.
	 * Available sizes: `small`, `medium`, `large`, `huge`
	 */
	#[Prop]
	protected string|null $size = null;

	protected string $value = '';

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
		return $this->buttons;
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
