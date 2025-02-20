<?php

namespace Kirby\Cms;

use Kirby\Content\MemoryStorage;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

class NewFile extends File
{
	use NewModelFixes;

	public static function create(array $props, bool $move = false): File
	{
		$props = static::normalizeProps($props);

		// create the basic file and a test upload object
		$file = static::factory([
			...$props,
			'content'      => null,
			'translations' => null,
		]);

		$upload = $file->asset($props['source']);

		// merge the content with the defaults
		$props['content'] = [
			...$file->createDefaultContent(),
			...$props['content'],
		];

		// make sure that a UUID gets generated
		// and added to content right away
		if (
			Uuids::enabled() === true &&
			empty($content['uuid']) === true
		) {
			// sets the current uuid if it is the exact same file
			if ($file->exists() === true) {
				$existing = $file->parent()->file($file->filename());

				if (
					$file->sha1() === $upload->sha1() &&
					$file->template() === $existing->template()
				) {
					// use existing content data if it is the exact same file
					$content = $existing->content()->toArray();
				}
			}

			$content['uuid'] = Uuid::generate();
		}

		// keep the initial storage class
		$storage = $file->storage()::class;

		// make sure that the temporary page is stored in memory
		$file->changeStorage(MemoryStorage::class);

		// inject the content
		$file->setContent($props['content']);

		// inject the translations
		$file->setTranslations($props['translations'] ?? null);

		// if the format is different from the original,
		// we need to already rename it so that the correct file rules
		// are applied
		$create = $file->blueprint()->create();

		// run the hook
		$arguments = compact('file', 'upload');
		return $file->commit('create', $arguments, function ($file, $upload) use ($create, $move, $storage) {
			// remove all public versions, lock and clear UUID cache
			$file->unpublish();

			// only move the original source if intended
			$method = $move === true ? 'move' : 'copy';

			// overwrite the original
			if (F::$method($upload->root(), $file->root(), true) !== true) {
				throw new LogicException(
					message: 'The file could not be created'
				);
			}

			// resize the file on upload if configured
			$file = $file->manipulate($create);

			// store the content if necessary
			$file->changeStorage($storage);

			// return a fresh clone
			return $file->clone();
		});
	}

	protected static function normalizeProps(array $props): array
	{
		if (isset($props['source'], $props['parent']) === false) {
			throw new InvalidArgumentException(
				message: 'Please provide the "source" and "parent" props for the File'
			);
		}

		$content  = $props['content']  ?? [];
		$template = $props['template'] ?? 'default';

		// prefer the filename from the props
		$filename ??= basename($props['source']);
		$filename   = F::safeName($props['filename']);

		return [
			...$props,
			'content'  => $content,
			'filename' => $filename,
			'model'    => $props['model'] ?? $template,
			'template' => $template,
		];
	}

}
