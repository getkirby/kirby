<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Writer Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class WriterField extends ProseMirrorField
{
	use Mixin\Counter;

	/**
	 * Available heading levels
	 */
	protected array|null $headings;

	/**
	 * Enables inline mode, which will not wrap new lines in paragraphs and creates hard breaks instead.
	 */
	protected bool|null $inline;

	public function __construct(
		bool|null $counter = null,
		array|null $headings = null,
		bool|null $inline = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->counter  = $counter;
		$this->headings = $headings;
		$this->inline   = $inline;
	}

	public function headings(): array
	{
		return array_intersect($this->headings ?? range(1, 6), range(1, 6));
	}

	public function inline(): bool
	{
		return $this->inline ?? false;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'counter'  => $this->counter(),
			'headings' => $this->headings(),
			'inline'   => $this->inline(),
		];
	}
}
