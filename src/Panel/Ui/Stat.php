<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class Stat extends Component
{
	public function __construct(
		public ModelWithContent $model,
		public array|string $label,
		public array|string $value,
		public string $component = 'k-stat',
		public string|null $icon = null,
		public array|string|null $info = null,
		public array|string|null $link = null,
		public string|null $theme = null,
	) {
	}

	public static function from(
		ModelWithContent $model,
		array|string $input
	): static {
		if (is_string($input) === true) {
			return static::fromQuery(
				model: $model,
				query: $input
			);
		}

		return new static(...[
			...$input,
			'model' => $model,
			'label' => $input['label'],
			'value' => $input['value'],
		]);
	}

	public static function fromQuery(
		ModelWithContent $model,
		string $query
	): static {
		$stat = $model->query($query);

		if (is_array($stat) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid data from stat query. The query must return an array.'
			);
		}

		return new static(...[
			...$stat,
			'model' => $model,
			'label' => $stat['label'],
			'value' => $stat['value'],
		]);
	}

	public function icon(): string|null
	{
		return $this->query($this->icon);
	}

	public function info(): string|null
	{
		return $this->query(
			$this->translate($this->info)
		);
	}

	public function label(): string
	{
		return $this->query(
			$this->translate($this->label)
		);
	}

	public function link(): string|null
	{
		return $this->query(
			$this->translate($this->link)
		);
	}

	public function props(): array
	{
		return [
			'icon'  => $this->icon(),
			'info'  => $this->info(),
			'label' => $this->label(),
			'link'  => $this->link(),
			'theme' => $this->theme(),
			'value' => $this->value(),
		];
	}

	protected function query(string|null $query): string|null
	{
		return $query === null ? null : $this->model->toString($query);
	}

	public function theme(): string|null
	{
		return $this->query($this->theme);
	}

	protected function translate(array|string|null $prop): string|null
	{
		if ($prop === null) {
			return $prop;
		}

		return I18n::translate($prop, $prop);
	}

	public function value(): string
	{
		return $this->query(
			$this->translate($this->value)
		);
	}
}
