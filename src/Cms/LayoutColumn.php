<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

/**
 * Represents a single layout column with
 * multiple blocks
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\Item<\Kirby\Cms\LayoutColumns>
 */
class LayoutColumn extends Item
{
	use HasMethods;

	public const ITEMS_CLASS = LayoutColumns::class;

	protected Blocks $blocks;
	protected string $width;

	/**
	 * Creates a new LayoutColumn object
	 */
	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->blocks = Blocks::factory($params['blocks'] ?? [], [
			'field'  => $this->field,
			'parent' => $this->parent
		]);

		$this->width = $params['width'] ?? '1/1';
	}

	/**
	 * Magic getter function
	 */
	public function __call(string $method, mixed $args): mixed
	{
		// layout column methods
		if ($this->hasMethod($method) === true) {
			return $this->callMethod($method, $args);
		}
	}

	/**
	 * Returns the blocks collection
	 *
	 * @param bool $includeHidden Sets whether to include hidden blocks
	 */
	public function blocks(bool $includeHidden = false): Blocks
	{
		if ($includeHidden === false) {
			return $this->blocks->filter('isHidden', false);
		}

		return $this->blocks;
	}

	/**
	 * Checks if the column is empty
	 * @since 3.5.2
	 */
	public function isEmpty(): bool
	{
		return $this
			->blocks()
			->filter('isHidden', false)
			->count() === 0;
	}

	/**
	 * Checks if the column is not empty
	 * @since 3.5.2
	 */
	public function isNotEmpty(): bool
	{
		return $this->isEmpty() === false;
	}

	/**
	 * Returns the number of columns this column spans
	 */
	public function span(int $columns = 12): int
	{
		$fraction = Str::split($this->width, '/');
		$a = $fraction[0] ?? 1;
		$b = $fraction[1] ?? 1;

		return $columns * $a / $b;
	}

	/**
	 * The result is being sent to the editor
	 * via the API in the panel
	 */
	public function toArray(): array
	{
		return [
			'blocks' => $this->blocks(true)->toArray(),
			'id'     => $this->id(),
			'width'  => $this->width(),
		];
	}

	/**
	 * Returns the width of the column
	 */
	public function width(): string
	{
		return $this->width;
	}
}
