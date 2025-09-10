<?php

namespace Kirby\Model;

use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\F;
use Kirby\Uuid\Uuid;
use Throwable;

class FileStorage extends Storage
{
	public function changeFilename(FileMeta $oldMeta, FileMeta $newMeta): void
	{
		$name      = F::name($newMeta->filename);
		$extension = F::extension($newMeta->filename);
		$directory = dirname($newMeta->identifier);

		$newMeta->identifier = $directory . '/' . $name . '.' . $extension;

		$this->moveIdentifier($oldMeta, $newMeta);
	}

	public function changeNum(FileMeta $oldMeta, FileMeta $newMeta): void
	{
		$this->update([
			'num' => $newMeta->num,
		]);
	}

	public function changeTemplate(FileMeta $oldMeta, FileMeta $newMeta): void
	{
		$this->update([
			'template' => $newMeta->template,
		]);
	}

	protected static function contentFile($meta): string
	{
		return $meta->identifier . '.txt';
	}

	public static function create(FileMeta $meta): File
	{
		// create the new identifier
		$meta->identifier = static::createIdentifier($meta);

		// generate the new UUID if it is not set yet
		$meta->uuid ??= Uuid::generate();

		// use the default template if it is not set yet
		$meta->template ??= 'default';

		if (is_file($meta->identifier) === true) {
			throw new DuplicateException('The file already exists');
		}

		// copy the source file to the new identifier
		F::copy($meta->source, $meta->identifier);

		// create the new content file
		Data::write(static::contentFile($meta), [
			'template' => $meta->template,
			'num'      => $meta->num,
			'uuid'     => $meta->uuid,
		]);

		return static::find(File::class, $meta->identifier);
	}

	protected static function createIdentifier(FileMeta $meta): string
	{
		// create the new identifier
		$identifier = $meta->parent->load()->meta()->identifier;

		// add the filename
		$identifier .= '/' . $meta->filename;

		return $identifier;
	}

	public static function find(string $class, string $identifier): Model|null
	{
		if (is_file($identifier) === false) {
			return null;
		}

		$meta = new FileMeta(
			filename: basename($identifier),
			identifier: $identifier,
			parent: FileParent::from(dirname($identifier)),
			source: $identifier,
		);

		try {
			$content	    = Data::read(static::contentFile($meta));
			$meta->num      = $content['num']      ?? null;
			$meta->template = $content['template'] ?? 'default';
			$meta->uuid     = $content['uuid']     ?? Uuid::generate();
		} catch (Throwable $e) {
		}

		return new $class(
			filename: $meta->filename,
			identifier: $meta->identifier,
			num: $meta->num,
			parent: $meta->parent,
			source: $meta->source,
			template: $meta->template,
			uuid: $meta->uuid,
		);
	}

	protected function moveIdentifier(FileMeta $oldMeta, FileMeta $newMeta): void
	{
		if ($oldMeta->identifier === $newMeta->identifier) {
			return;
		}

		F::move($oldMeta->identifier, $newMeta->identifier);
		F::move($this->contentFile($oldMeta), $this->contentFile($newMeta));
	}
}
