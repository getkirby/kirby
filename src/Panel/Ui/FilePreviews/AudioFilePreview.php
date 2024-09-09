<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
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
