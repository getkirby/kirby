<?php

namespace Kirby\Model;

use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Throwable;

abstract class Storage
{
	public function __construct(
		protected Model $model,
	) {}

	abstract protected static function contentFile($meta): string;

	public function children(): array
	{
		throw new LogicException('This model does not have children');
	}

	public function files(): array
	{
		throw new LogicException('This model does not have files');
	}

	abstract public static function find(string $class, string $identifier): Model|null;

	public function read(): array
	{
		try {
			return Data::read($this->contentFile($this->model->meta()));
		} catch (Throwable $e) {
			return [];
		}
	}

	public function changeMeta(array $meta): ModelMeta
	{
		$newMeta = clone $this->model->meta();

		foreach ($meta as $key => $value) {
			$newMeta->$key = $value;
		}

		foreach (array_keys($meta) as $key) {
			$this->{'change' . ucfirst($key)}($this->model->meta(), $newMeta);
		}

		return $newMeta;
	}

	public function update(array $data): void
	{
		$content = [
			...$this->read(),
			...$data,
		];

		$this->write($content);
	}

	public function write(array $data): void
	{
		Data::write($this->contentFile($this->model->meta()), $data);
	}
}
