<?php

namespace Kirby\Panel\Ui\FilePreview;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 *
 * @unstable
 */
class AudioFilePreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-audio-file-preview'
	) {
	}

	public static function accepts(File $file): bool
	{
		return $file->type() === 'audio';
	}
}
