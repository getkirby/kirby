<?php

namespace Kirby\Content;

use Exception;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;

class TestStorage extends Storage
{
	public array $store = [];

	public function __construct(protected ModelWithContent $model)
	{
		$this->store = [];
	}

	public function delete(VersionId $versionId, Language $language): void
	{
		unset($this->store[$this->key($versionId, $language)]);
	}

	public function exists(VersionId $versionId, Language $language): bool
	{
		return isset($this->store[$this->key($versionId, $language)]);
	}

	public function key(VersionId $versionId, Language $language): string
	{
		return $versionId . '/' . $language;
	}

	public function modified(VersionId $versionId, Language $language): int|null
	{
		throw new Exception('Not implemented');
	}

	public function read(VersionId $versionId, Language $language): array
	{
		return $this->store[$this->key($versionId, $language)] ?? [];
	}

	public function touch(VersionId $versionId, Language $language): void
	{
		throw new Exception('Not implemented');
	}

	public function write(VersionId $versionId, Language $language, array $fields): void
	{
		$this->store[$this->key($versionId, $language)] = $fields;
	}
}
