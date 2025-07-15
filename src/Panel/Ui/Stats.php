<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Exception\InvalidArgumentException;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class Stats extends Component
{
	public function __construct(
		public ModelWithContent $model,
		public string $component = 'k-stats',
		public array $reports = [],
		public string $size = 'large',
	) {
	}

	public static function from(
		ModelWithContent $model,
		array|string $reports,
		string $size = 'large'
	): static {
		if (is_string($reports) === true) {
			$reports = $model->query($reports);

			if (is_array($reports) === false) {
				throw new InvalidArgumentException(
					message: 'Invalid data from stats query. The query must return an array.'
				);
			}
		}

		return new static(
			model: $model,
			reports: $reports,
			size: $size
		);
	}

	public function props(): array
	{
		return [
			'reports' => $this->reports(),
			'size'    => $this->size(),
		];
	}

	public function reports(): array
	{
		$reports = [];

		foreach ($this->reports as $report) {
			try {
				$stat = Stat::from(
					model: $this->model,
					input: $report
				);
			} catch (InvalidArgumentException) {
				continue;
			}

			$reports[] = $stat->props();
		}

		return $reports;
	}

	public function size(): string
	{
		return $this->size;
	}
}
