<?php

namespace Kirby\Model;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;
use Throwable;

class PageStorage extends Storage
{
	/**
	 * @var Page
	 */
	protected Model $model;

	public function __construct(
		Page $page,
	) {
		parent::__construct($page);
	}

	public function changeNum(PageMeta $oldMeta, PageMeta $newMeta): void
	{
		$newMeta->identifier = static::dir($newMeta);
		$this->moveIdentifier($oldMeta, $newMeta);
	}

	public function changeSlug(PageMeta $oldMeta, PageMeta $newMeta): void
	{
		$newMeta->identifier = static::dir($newMeta);
		$this->moveIdentifier($oldMeta, $newMeta);
	}

	public function changeTemplate(PageMeta $oldMeta, PageMeta $newMeta): void
	{
		$oldFile = $oldMeta->identifier . '/' . $oldMeta->template . '.txt';
		$newFile = $newMeta->identifier . '/' . $newMeta->template . '.txt';

		F::move($oldFile, $newFile);
	}

	public function children(): array
	{
		$root     = $this->model->meta()->identifier;
		$children = Dir::dirs($root);
		$children = array_filter($children, fn($child) => str_starts_with($child, '_') === false);
		$children = array_map(fn($child) => $root . '/' . $child, $children);

		return $children;
	}

	public static function create(PageMeta $meta): Page
	{
		// create the new identifier
		$meta->identifier = static::createIdentifier($meta);

		if (is_dir($meta->identifier) === true) {
			throw new DuplicateException('The page already exists');
		}

		// create the new directory
		Dir::make($meta->identifier);

		// create the new content file
		Data::write(static::contentFile($meta), [
			'uuid' => $meta->uuid,
		]);

		return static::find(Page::class, $meta->identifier);
	}

	protected static function createIdentifier(PageMeta $meta): string
	{
		// create the new identifier
		$identifier = $meta->parent->load()?->meta()->identifier ?? App::instance()->root('content');

		// add the draft status if it is a draft
		if ($meta->status === PageStatus::Draft) {
			$identifier .= '/_drafts';
		}

		// add the slug and num
		$identifier .= '/' . static::dirname($meta);

		return $identifier;
	}

	protected static function dir(PageMeta $meta): string
	{
		return dirname($meta->identifier) . '/' . static::dirname($meta);
	}

	protected static function dirname(PageMeta $meta): string
	{
		return $meta->num ? $meta->num . '_' . $meta->slug : $meta->slug;
	}

	protected static function contentFile($meta): string
	{
		return $meta->identifier . '/' . $meta->template . '.txt';
	}

	public function files(): array
	{
		$root  = $this->model->meta()->identifier;
		$files = Dir::files($root);
		$files = array_filter($files, fn($file) => str_ends_with($file, '.txt') === false);
		$files = array_map(fn($file) => $root . '/' . $file, $files);

		return $files;
	}

	public static function find(string $class, string $identifier): Model|null
	{
		if (is_dir($identifier) === false) {
			return null;
		}

		$info = [
			...static::parsePageIdentifier($identifier),
			...static::parsePageDirectory($identifier),
		];

		$meta = new PageMeta(
			identifier: $identifier,
			num: $info['num'],
			parent: PageParent::from($info['parent']),
			slug: $info['slug'],
			status: $info['status'],
			template: $info['template'],
		);

		try {
			$content    = Data::read(static::contentFile($meta));
			$meta->slug = $content['slug'] ?? $meta->slug;
			$meta->uuid = $content['uuid'] ?? Uuid::generate();
		} catch (Throwable $e) {
			return null;
		}

		return new $class(
			identifier: $meta->identifier,
			num: $meta->num,
			parent: $meta->parent,
			slug: $meta->slug,
			status: $meta->status,
			template: $meta->template,
			uuid: $meta->uuid,
		);
	}

	protected function moveIdentifier(PageMeta $oldMeta, PageMeta $newMeta): void
	{
		if ($oldMeta->identifier === $newMeta->identifier) {
			return;
		}

		Dir::move($oldMeta->identifier, $newMeta->identifier);
	}

	protected static function parsePageDirectory(string $directory): array
	{
		$extension = App::instance()->contentExtension();
		$files     = Dir::read($directory);
		$txts      = array_values(array_filter($files, fn($file) => Str::endsWith($file, $extension)));

		return [
			'template' => F::name($txts[0]),
		];
	}

	protected static function parsePageIdentifier(string $identifier): array
	{
		['parent' => $parent, 'status' => $status] = static::parsePageParent($identifier);

		$dirname = basename($identifier);
		$parts   = Str::split($dirname, '_');

		if (count($parts) === 1) {
			$slug     = $parts[0];
			$num      = null;
			$status ??= PageStatus::Unlisted;
		} elseif (count($parts) === 2) {
			$slug     = $parts[1];
			$num      = $parts[0];
			$status ??= PageStatus::Listed;
		} else {
			throw new LogicException('Invalid directory name: ' . $dirname);
		}

		return [
			'num'    => $num,
			'parent' => $parent,
			'slug'   => $slug,
			'status' => $status,
		];
	}

	protected static function parsePageParent(string $identifier): array
	{
		$parent = dirname($identifier);
		$status = null;

		if ($parent === App::instance()->root('content')) {
			$parent = null;
		} else if (basename($parent) === '_drafts') {
			$parent = dirname($parent);
			$status = PageStatus::Draft;
		}

		return [
			'parent' => $parent,
			'status' => $status,
		];
	}
}
