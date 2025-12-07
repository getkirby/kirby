<?php

namespace Kirby\Form\Field;

use Kirby\Cms\FilePicker;
use Kirby\Form\Mixin;

/**
 * Textarea Field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
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

	protected mixed $value = '';

	public function __construct(
		bool|null $autofocus = null,
		array|bool|null $buttons = null,
		bool|null $counter = null,
		mixed $default = null,
		bool|null $disabled = null,
		mixed $files = null,
		array|string|null $help = null,
		string|null $font = null,
		array|string|null $label = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		string|null $name = null,
		bool|null $required = null,
		string|null $size = null,
		bool|null $spellcheck = null,
		bool|null $translate = null,
		mixed $uploads = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			help: $help,
			label: $label,
			name: $name,
			required: $required,
			translate: $translate,
			when: $when,
			width: $width
		);

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
				'pattern' => 'files',
				'action'  => function () use ($field) {
					/**
					 * @var \Kirby\Api\Api
					 */
					$api = $this;

					return $field->filepicker(
						$api->requestQuery('page'),
						$api->requestQuery('search')
					);
				}
			],
			[
				'pattern' => 'upload',
				'method'  => 'POST',
				'action'  => function () use ($field) {
					/**
					 * @var \Kirby\Api\Api
					 */
					$api = $this;

					return $field->upload(
						$api,
						$field->uploads(),
						fn ($file, $parent) => [
							'filename' => $file->filename(),
							'dragText' => $file->panel()->dragText(
								absolute: $field->model()->is($parent) === false
							),
						]
					);
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
		return $this->default ? trim($this->default) : null;
	}

	public function filepicker(int|null $page, string|null $search): array
	{
		$settings = match (true) {
			is_string($this->files) => ['query' => $this->files],
			is_array($this->files)  => $this->files,
			default                 => [],
		};

		return (new FilePicker([
			...$settings,
			'model' => $this->model(),
			'page'   => $page,
			'search' => $search
		]))->toArray();
	}

	/**
	 * @psalm-suppress MethodSignatureMismatch
	 * @todo Remove psalm suppress after https://github.com/vimeo/psalm/issues/8673 is fixed
	 */
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
