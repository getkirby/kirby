<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\HasStringTemplate;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class Stat extends Component
{
	use HasStringTemplate;

	public function __construct(
		public array|string $label,
		public array|string $value,
		public string $component = 'k-stat',
		public array|string|null $dialog = null,
		public array|string|null $drawer = null,
		public string|null $icon = null,
		public array|string|null $info = null,
		public array|string|null $link = null,
		public ModelWithContent|null $model = null,
		public string|null $theme = null,
	) {
	}

	public function dialog(): string|null
	{
		return $this->stringTemplate($this->dialog, safe: false);
	}

	public function drawer(): string|null
	{
		return $this->stringTemplate($this->drawer, safe: false);
	}

	public static function from(
		array|string $input,
		ModelWithContent|null $model = null,
	): static {
		if ($model !== null) {
			if (is_string($input) === true) {
				$input = $model->query($input);

				if (is_array($input) === false) {
					throw new InvalidArgumentException(
						message: 'Invalid data from stat query. The query must return an array.'
					);
				}
			}

			$input['model'] = $model;
		}

		return new static(...$input);
	}

	public function icon(): string|null
	{
		return $this->stringTemplate($this->icon, safe: false);
	}

	public function info(): string|null
	{
		return $this->stringTemplateI18n($this->info, safe: false);
	}

	public function label(): string
	{
		return $this->stringTemplateI18n($this->label, safe: false);
	}

	public function link(): string|null
	{
		return $this->stringTemplate($this->link, safe: false);
	}

	public function props(): array
	{
		return [
			'dialog' => $this->dialog(),
			'drawer' => $this->drawer(),
			'icon'   => $this->icon(),
			'info'   => $this->info(),
			'label'  => $this->label(),
			'link'   => $this->link(),
			'theme'  => $this->theme(),
			'value'  => $this->value(),
		];
	}

	public function theme(): string|null
	{
		return $this->stringTemplate($this->theme, safe: false);
	}

	public function value(): string
	{
		return $this->stringTemplateI18n($this->value, safe: false);
	}
}
