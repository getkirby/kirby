<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use Kirby\Filesystem\F;

/**
 * Plain text storage that simulates a parallel request
 * removing the changes version between a check and its use
 */
class DiscardingStorage extends PlainTextStorage
{
	/**
	 * Removes the changes file right after the
	 * next existence check that finds it
	 */
	public static bool $discard = false;

	public function exists(VersionId $versionId, Language $language): bool
	{
		$exists = parent::exists($versionId, $language);

		if (
			static::$discard === true &&
			$exists === true &&
			$versionId->is('changes') === true
		) {
			static::$discard = false;
			F::unlink($this->contentFile($versionId, $language));
		}

		return $exists;
	}
}
