<?php

namespace Kirby\Panel\Ui\FilePreview;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;
use Override;

/**
 * Fallback file preview component
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @unstable
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
	#[Override]
	public static function accepts(File $file): bool
	{
		return true;
	}

	#[Override]
	public function props(): array
	{
		return [
			...parent::props(),
			'image' => $this->image()
		];
	}
}
