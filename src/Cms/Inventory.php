<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;

/**
 * Scans a directory for the contents of a page, site or user:
 * children, files, content files and the resolved template;
 * the page model of a child is resolved separately in `::model()`
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.5.0
 */
class Inventory
{
	/**
	 * Scans the directory and analyzes files,
	 * content, meta info and children
	 *
	 * @return array{children: list<array>, files: array<string, array>, template: string}
	 */
	public static function for(
		string $dir,
		string $extension = 'txt',
		array|null $ignore = null,
		bool $multilang = false
	): array {
		$inventory = [
			'children' => [],
			'files'    => [],
			'template' => 'default',
		];

		$dir = realpath($dir);

		if ($dir === false) {
			return $inventory;
		}

		// a temporary store for all content files
		$content = [];

		// read and sort all items naturally to avoid sorting issues later
		$items = Dir::read($dir, $ignore);
		natsort($items);

		// loop through all directory items and collect all relevant information
		foreach ($items as $item) {
			// ignore all items with a leading dot or underscore
			if (
				str_starts_with($item, '.') ||
				str_starts_with($item, '_')
			) {
				continue;
			}

			$root = $dir . '/' . $item;

			// collect all directories as children
			if (is_dir($root) === true) {
				$inventory['children'][] = static::child($item, $root);
				continue;
			}

			$itemExt = pathinfo($item, PATHINFO_EXTENSION);

			// don't track files with these extensions
			if (in_array($itemExt, ['htm', 'html', 'php'], true) === true) {
				continue;
			}

			// collect all content files separately,
			// not as inventory entries
			if ($itemExt === $extension) {
				$filename = pathinfo($item, PATHINFO_FILENAME);

				// remove the language codes from all content filenames
				if ($multilang === true) {
					$filename = pathinfo($filename, PATHINFO_FILENAME);
				}

				$content[] = $filename;
				continue;
			}

			// collect all other files
			$inventory['files'][$item] = [
				'filename'  => $item,
				'extension' => $itemExt,
				'root'      => $root,
			];
		}

		$content = array_unique($content);

		$inventory['template'] = static::template(
			$content,
			$inventory['files']
		);

		return $inventory;
	}

	/**
	 * Collect information for a child for the inventory
	 */
	protected static function child(
		string $item,
		string $root
	): array {
		// extract the slug and num of the directory
		// TODO: Switch to static::$numSeparator in v6
		if ($separator = strpos($item, Dir::$numSeparator)) {
			$num  = (int)substr($item, 0, $separator);
			$slug = substr($item, $separator + 1);
		}

		return [
			'dirname' => $item,
			'num'     => $num ?? null,
			'root'    => $root,
			'slug'    => $slug ?? $item,
		];
	}

	/**
	 * Determines the page model of a child directory by looking
	 * for a content file of one of the registered page models
	 *
	 * This is deliberately not part of the directory scan: it
	 * costs a file check per registered model, which would be
	 * paid for every child even if the page is never created
	 * @since 6.0.0
	 */
	public static function model(
		string $root,
		string $extension = 'txt',
		bool $multilang = false
	): string|null {
		if (Page::$models === []) {
			return null;
		}

		if ($multilang === true) {
			$code      = App::instance()->defaultLanguage()->code();
			$extension = $code . '.' . $extension;
		}

		// look if a content file can be found
		// for any of the available models
		foreach (Page::$models as $name => $class) {
			if (is_file($root . '/' . $name . '.' . $extension) === true) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * Determines the main template for the inventory
	 * from all collected content files, ignoring meta files
	 */
	protected static function template(
		array $content,
		array $files,
	): string {
		foreach ($content as $name) {
			// is a meta file corresponding to an actual file, i.e. cover.jpg
			if (isset($files[$name]) === true) {
				continue;
			}

			// it's most likely the template
			// (will overwrite and use the last match for historic reasons)
			$template = $name;
		}

		return $template ?? 'default';
	}
}
