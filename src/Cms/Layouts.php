<?php

namespace Kirby\Cms;

use Kirby\Data\Json;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * A collection of layouts
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\Items<\Kirby\Cms\Layout>
 */
class Layouts extends Items
{
	public const ITEM_CLASS = Layout::class;

	/**
	 * All registered layouts methods
	 */
	public static array $methods = [];

	public static function factory(
		array|null $items = null,
		array $params = []
	): static {
		// convert single layout to layouts array
		if (
			isset($items['columns']) === true ||
			isset($items['id']) === true
		) {
			$items = [$items];
		}

		$first = $items[0] ?? [];

		// if there are no wrapping layouts for blocks yet …
		if (
			isset($first['content']) === true ||
			isset($first['type']) === true
		) {
			$items = [
				[
					'id'      => Str::uuid(),
					'columns' => [
						[
							'width'  => '1/1',
							'blocks' => $items
						]
					]
				]
			];
		}

		return parent::factory($items, $params);
	}

	/**
	 * Checks if a given block type exists in the layouts collection
	 * @since 3.6.0
	 */
	public function hasBlockType(string $type): bool
	{
		return $this->toBlocks()->hasType($type);
	}

	/**
	 * Parse layouts data
	 */
	public static function parse(array|string|null $input): array
	{
		if (
			empty($input) === false &&
			is_array($input) === false
		) {
			try {
				$input = Json::decode((string)$input);
			} catch (Throwable) {
				return [];
			}
		}

		if (empty($input) === true) {
			return [];
		}

		return $input;
	}

	/**
	 * Converts layouts to blocks
	 * @since 3.6.0
	 *
	 * @param bool $includeHidden Sets whether to include hidden blocks
	 */
	public function toBlocks(bool $includeHidden = false): Blocks
	{
		$blocks = [];

		if ($this->isNotEmpty() === true) {
			foreach ($this->data() as $layout) {
				foreach ($layout->columns() as $column) {
					foreach ($column->blocks($includeHidden) as $block) {
						$blocks[] = $block->toArray();
					}
				}
			}
		}

		return Blocks::factory($blocks, [
			'field'  => $this->field,
			'parent' => $this->parent
		]);
	}
}
