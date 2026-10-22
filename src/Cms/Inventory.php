<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;

/**
 * Scans a directory for the contents of a page, site or user:
 * children, files, content files and the resolved template/model
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.5.0
 */
class Inventory
{
	/**
	 * Resolved children
	 */
	protected array|null $children = null;

	/**
	 * Names of all content files, without language codes
	 */
	protected array $content = [];

	/**
	 * Cached `is_dir()` results by item
	 */
	protected array $dirs = [];

	/**
	 * Resolved files
	 */
	protected array|null $files = null;

	/**
	 * All items except content files, by name with their extension;
	 * split into children and files only when those are requested
	 */
	protected array $items = [];

	/**
	 * Separator between num and slug in page directory names
	 * (e.g. `1_about` → num: 1, slug: 'about')
	 * @since 6.0.0
	 */
	public static string $numSeparator = '_';

	/**
	 * @since 6.0.0
	 */
	public function __construct(
		protected string $dir,
		protected string $extension = 'txt',
		array|null $ignore = null,
		protected bool $multilang = false
	) {
		$root = realpath($dir);

		if ($root === false) {
			return;
		}

		$this->dir = $root;

		// read and sort all items naturally to avoid sorting issues later
		$items = Dir::read($root, $ignore);
		natsort($items);

		foreach ($items as $item) {
			// ignore all items with a leading dot or underscore
			if (
				str_starts_with($item, '.') ||
				str_starts_with($item, '_')
			) {
				continue;
			}

			$itemExt = pathinfo($item, PATHINFO_EXTENSION);

			// content files are never directories,
			// no need to ask the filesystem
			if ($itemExt === $this->extension) {
				$filename = pathinfo($item, PATHINFO_FILENAME);

				// remove the language codes from all content filenames
				if ($multilang === true) {
					$filename = pathinfo($filename, PATHINFO_FILENAME);
				}

				$this->content[] = $filename;
				continue;
			}

			$this->items[$item] = $itemExt;
		}

		$this->content = array_unique($this->content);
	}

	/**
	 * Collect information for a child for the inventory
	 *
	 * @param $models Models to look for, in the order they are tried
	 */
	protected function child(string $item, array $models): array
	{
		// extract the slug and num of the directory
		if ($separator = strpos($item, static::$numSeparator)) {
			$num  = (int)substr($item, 0, $separator);
			$slug = substr($item, $separator + 1);
		}

		$root      = $this->dir . '/' . $item;
		$extension = $this->extension;

		// in multi-language setups, the model is
		// determined by the default language's content file
		if ($this->multilang === true && $models !== []) {
			$extension = App::instance()->defaultLanguage()->code() . '.' . $extension;
		}

		// determine the model by looking if a content file
		// can be found for any of the available models
		foreach ($models as $name => $class) {
			if (is_file($root . '/' . $name . '.' . $extension) === true) {
				$model = $name;
				break;
			}
		}

		return [
			'dirname' => $item,
			'model'   => $model ?? null,
			'num'     => $num ?? null,
			'root'    => $root,
			'slug'    => $slug ?? $item,
		];
	}

	/**
	 * Returns all subdirectories as children
	 * @since 6.0.0
	 */
	public function children(): array
	{
		if ($this->children !== null) {
			return $this->children;
		}

		$this->children = [];

		// models to match children against, most common first
		$models = Page::$models;
		$counts = [];

		foreach (array_keys($this->items) as $item) {
			if ($this->isDir($item) === false) {
				continue;
			}

			$child = $this->children[] = $this->child($item, $models);

			// siblings often share the same models,
			// so try the most common ones first for the next child
			if ($child['model'] !== null) {
				$counts[$child['model']] = ($counts[$child['model']] ?? 0) + 1;

				if ($child['model'] !== array_key_first($models)) {
					arsort($counts);
					// keys keep the order of $counts, values (classes)
					// come from $models; models not seen yet go last
					$models = array_replace($counts, $models);
				}
			}
		}

		return $this->children;
	}

	/**
	 * Returns all files, without content files
	 * @since 6.0.0
	 */
	public function files(): array
	{
		if ($this->files !== null) {
			return $this->files;
		}

		$this->files = [];

		foreach ($this->items as $item => $extension) {
			if ($this->isFile($item) === true) {
				$this->files[$item] = [
					'filename'  => $item,
					'extension' => $extension,
					'root'      => $this->dir . '/' . $item,
				];
			}
		}

		return $this->files;
	}

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
		return (new static($dir, $extension, $ignore, $multilang))->toArray();
	}

	/**
	 * Whether the item is a directory
	 * @since 6.0.0
	 */
	protected function isDir(string $item): bool
	{
		return $this->dirs[$item] ??= is_dir($this->dir . '/' . $item);
	}

	/**
	 * Whether the item is tracked as file
	 * @since 6.0.0
	 */
	protected function isFile(string $item): bool
	{
		// don't track files with these extensions
		if (in_array($this->items[$item], ['htm', 'html', 'php'], true) === true) {
			return false;
		}

		return $this->isDir($item) === false;
	}

	/**
	 * Determines the main template from all
	 * content files, ignoring meta files
	 * @since 6.0.0
	 */
	public function template(): string
	{
		// the last match wins for historic reasons,
		// unless it is the meta file of a file, i.e. cover.jpg
		foreach (array_reverse($this->content) as $name) {
			if (
				isset($this->items[$name]) === false ||
				$this->isFile($name) === false
			) {
				return $name;
			}
		}

		return 'default';
	}

	/**
	 * @since 6.0.0
	 */
	public function toArray(): array
	{
		return [
			'children' => $this->children(),
			'files'    => $this->files(),
			'template' => $this->template(),
		];
	}
}
