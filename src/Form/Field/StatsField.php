<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Form\FieldClass;
use Kirby\Form\Fields;
use Kirby\Panel\Ui\Stats;

/**
 * Stats field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class StatsField extends FieldClass
{
	/**
	 * Array or query string for reports. Each report needs a `label` and `value` and can have additional `info`, `link`, `icon` and `theme` settings.
	 */
	protected array|string $reports;

	/**
	 * The size of the report cards. Available sizes: `tiny`, `small`, `medium`, `large`
	 */
	protected string $size;

	/**
	 * Cache for the Stats UI component
	 */
	protected Stats $stats;

	public function __construct(
		array|string|null $after = null,
		bool $autofocus = false,
		array|string|null $before = null,
		mixed $default = null,
		bool $disabled = false,
		array|string|null $help = null,
		bool $hidden = false,
		string|null $icon = null,
		string|null $label = null,
		ModelWithContent|null $model = null,
		string|null $name = null,
		array|string|null $placeholder = null,
		array|string $reports = [],
		bool $required = false,
		Fields|null $siblings = null,
		string $size = 'large',
		bool $translate = true,
		array|null $when = null,
		string|null $width = null,
		mixed $value = null,
		// additional parameters can be passed to the field
		...$params
	) {
		parent::__construct(
			...$params,
			after: $after,
			autofocus: $autofocus,
			before: $before,
			default: $default,
			disabled: $disabled,
			help: $help,
			hidden: $hidden,
			icon: $icon,
			label: $label,
			model: $model,
			name: $name,
			placeholder: $placeholder,
			required: $required,
			siblings: $siblings,
			translate: $translate,
			when: $when,
			width: $width,
			value: $value
		);

		$this->setReports($reports);
		$this->setSize($size);
	}

	public function hasValue(): bool
	{
		return false;
	}

	public function reports(): array
	{
		return $this->stats()->reports();
	}

	protected function setReports(array|string $reports): void
	{
		$this->reports = $reports;
	}

	protected function setSize(string $size = 'large'): void
	{
		$this->size = $size;
	}

	public function size(): string
	{
		return $this->stats()->size();
	}

	public function stats(): Stats
	{
		return $this->stats ??= Stats::from(
			model: $this->model,
			reports: $this->reports,
			size: $this->size
		);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			...$this->stats()->props()
		];
	}
}
