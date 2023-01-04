<?php

namespace Kirby\Cms;

class GhostPage extends Page
{
	public function readContent(string $languageCode = null): array
	{
		return $this->propertyData['content'] ?? [];
	}

	public function writeContent(array $data, string $languageCode = null): bool
	{
		return true;
	}
}
