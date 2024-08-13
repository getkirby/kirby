<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;

/**
 * Fallback file preview component
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class DefaultFilePreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-default-file-preview'
	) {
	}

	/**
	 * Accepts any file as last resort
	 */
	public static function accepts(File $file): bool
	{
		return true;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'image' => $this->image()
		];
	}
}
