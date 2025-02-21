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

	public function changeTemplate(string|null $template): static
	{
		if ($template === $this->template()) {
			return $this;
		}

		$arguments = [
			'file'     => $this,
			'template' => $template ?? 'default'
		];

		return $this->commit('changeTemplate', $arguments, function ($oldFile, $template) {
			// convert to new template/blueprint incl. content
			$file = $oldFile->convertTo($template);

			// update template, prefer unset over writing `default`
			if ($template === 'default') {
				$template = null;
			}

			// Use the version class to update the template
			// If we use the $file->update() method directly, the Form class
			// will still use the old blueprint and might write invalid data
			// to the content file. We also don't want to trigger update hooks.
			$file->version()->save(
				['template' => $template],
				'default'
			);

			// resize the file if configured by new blueprint
			$create = $file->blueprint()->create();
			$file   = $file->manipulate($create);

			return $file;
		});
	}

	/**
	 * Store the template in addition to the
	 * other content.
	 * @internal
	 */
	public function contentFileData(
		array $data,
		string|null $languageCode = null
	): array {
		$language = Language::ensure($languageCode);

		// only keep the template and sort fields in the
		// default language
		if ($language->isDefault() === false) {
			unset($data['template'], $data['sort']);
			return $data;
		}

		// only add the template in, if the $data array
		// doesn't explicitly unsets it
		if (
			array_key_exists('template', $data) === false &&
			$template = $this->template()
		) {
			$data['template'] = $template;
		}

		return $data;
	}

	/**
	 * Copy the file to the given page
	 * @internal
	 *
	 * @psalm-suppress MethodSignatureMismatch
	 */
	public function copy(Page $page): static
	{
		F::copy($this->root(), $page->root() . '/' . $this->filename());

		$copy = new static([
			'parent'   => $page,
			'filename' => $this->filename(),
		]);

		$this->storage()->copyAll(to: $copy->storage());

		// overwrite with new UUID (remove old, add new)
		if (Uuids::enabled() === true) {
			$copy = $copy->save(['uuid' => Uuid::generate()]);
		}

		return $copy;
	}

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
			empty($props['content']['uuid']) === true
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

			$props['content']['uuid'] = Uuid::generate();
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
		$filename   = $props['filename'] ?? null;
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
