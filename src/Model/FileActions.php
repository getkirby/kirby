<?php

namespace Kirby\Model;

trait FileActions
{
	public function changeFilename(string $filename): static
	{
		$this->meta = $this->storage()->changeMeta([
			'filename' => $filename,
		]);

		return $this;
	}

	public function changeName(string $name): static
	{
		$this->meta = $this->storage()->changeMeta([
			'filename' => $name . '.' . $this->extension(),
		]);

		return $this;
	}

	public function changeExtension(string $extension): static
	{
		$this->meta = $this->storage()->changeMeta([
			'filename' => $this->name() . '.' . $extension,
		]);

		return $this;
	}

	public static function create(
		string $filename,
		string $source,
		FileParent|Model|string $parent,
		int|null $num = null,
		string $template = 'default',
		string|null $uuid = null,
	): static {
		$meta = new FileMeta(
			filename: $filename,
			identifier: '__NEW__',
			parent: FileParent::from($parent),
			source: $source,
			num: $num,
			template: $template,
			uuid: $uuid,
		);

		return static::STORAGE::create($meta);
	}
}
