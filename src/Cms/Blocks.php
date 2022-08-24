<?php

namespace Kirby\Cms;

use Kirby\Data\Json;
use Kirby\Parsley\Parsley;
use Kirby\Parsley\Schema\Blocks as BlockSchema;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * A collection of blocks
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Blocks extends Items
{
	public const ITEM_CLASS = '\Kirby\Cms\Block';

	/**
	 * Return HTML when the collection is
	 * converted to a string
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toHtml();
	}

	/**
	 * Converts the blocks to HTML and then
	 * uses the Str::excerpt method to create
	 * a non-formatted, shortened excerpt from it
	 *
	 * @param mixed ...$args
	 * @return string
	 */
	public function excerpt(...$args)
	{
		return Str::excerpt($this->toHtml(), ...$args);
	}

	/**
	 * Wrapper around the factory to
	 * catch blocks from layouts
	 *
	 * @param array $items
	 * @param array $params
	 * @return \Kirby\Cms\Blocks
	 */
	public static function factory(array $items = null, array $params = [])
	{
		$items = static::extractFromLayouts($items);

		return parent::factory($items, $params);
	}

	/**
	 * Pull out blocks from layouts
	 *
	 * @param array $input
	 * @return array
	 */
	protected static function extractFromLayouts(array $input): array
	{
		if (empty($input) === true) {
			return [];
		}

		// no columns = no layout
		if (array_key_exists('columns', $input[0]) === false) {
			return $input;
		}

		$blocks = [];

		foreach ($input as $layout) {
			foreach (($layout['columns'] ?? []) as $column) {
				foreach (($column['blocks'] ?? []) as $block) {
					$blocks[] = $block;
				}
			}
		}

		return $blocks;
	}

	/**
	 * Checks if a given block type exists in the collection
	 * @since 3.6.0
	 *
	 * @param string $type
	 * @return bool
	 */
	public function hasType(string $type): bool
	{
		return $this->filterBy('type', $type)->count() > 0;
	}

	/**
	 * Parse and sanitize various block formats
	 *
	 * @param array|string $input
	 * @return array
	 */
	public static function parse($input): array
	{
		if (empty($input) === false && is_array($input) === false) {
			try {
				$input = Json::decode((string)$input);
			} catch (Throwable) {
				$parser = new Parsley((string)$input, new BlockSchema());
				$input  = $parser->blocks();
			}
		}

		if (empty($input) === true) {
			return [];
		}

		return $input;
	}

	/**
	 * Convert all blocks to HTML
	 *
	 * @return string
	 */
	public function toHtml(): string
	{
		$html = [];

		foreach ($this->data as $block) {
			$html[] = $block->toHtml();
		}

		return implode($html);
	}
}
