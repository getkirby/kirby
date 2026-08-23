<?php

namespace Kirby\Form\Field;

use Kirby\Panel\Ui\Stats;

/**
 * Stats field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class StatsField extends DisplayField
{
	/**
	 * Array or query string for reports. Each report needs a `label` and `value` and can have additional `info`, `link`, `icon` and `theme` settings.
	 */
	protected array|string|null $reports;

	/**
	 * The size of the report cards. Available sizes: `tiny`, `small`, `medium`, `large`
	 */
	protected string|null $size;

	/**
	 * Cache for the Stats UI component
	 */
	protected Stats|null $stats = null;

	public function __construct(
		array|string|null $reports = null,
		string|null $size = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

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
			model:   $this->model,
			reports: $this->reports ?? [],
			size:    $this->size ?? 'large'
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
