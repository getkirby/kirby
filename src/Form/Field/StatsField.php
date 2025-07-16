<?php

namespace Kirby\Form\Field;

use Kirby\Cms\ModelWithContent;
use Kirby\Form\FieldAbstract\DisplayField;
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
class StatsField extends DisplayField
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
	 * Cache for the stats object
	 */
	protected Stats $stats;

	public function __construct(
		bool $disabled = false,
		array|string|null $label = null,
		array|string|null $help = null,
		ModelWithContent|null $model = null,
		string|null $name = null,
		array|string $reports = [],
		Fields|null $siblings = null,
		string $size = 'large',
		array|null $when = null,
		string|null $width = null,
	) {
		parent::__construct(
			disabled: $disabled,
			help: $help,
			label: $label,
			model: $model,
			name: $name,
			siblings: $siblings,
			when: $when,
			width: $width
		);

		$this->reports = $reports;
		$this->size    = $size;
	}

	public function reports(): array
	{
		return $this->stats()->reports();
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
