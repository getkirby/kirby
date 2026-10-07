<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\File;
use Kirby\Image\Svg;
use Kirby\Panel\Ui\FilePreview;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @unstable
 */
class ImageFilePreview extends FilePreview
{
	public function __construct(
		public File $file,
		public string $component = 'k-image-file-preview'
	) {
	}

	public static function accepts(File $file): bool
	{
		return $file->type() === 'image';
	}

	public function details(): array
	{
		return [
			...parent::details(),
			[
				'title' => I18n::translate('dimensions'),
				'text'  => $this->file->dimensions() . ' ' . I18n::translate('pixel')
			],
			[
				'title' => I18n::translate('orientation'),
				'text'  => I18n::translate('orientation.' . $this->file->dimensions()->orientation())
			]
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'focusable' => $this->file->panel()->isFocusable(),
			'ratio'     => $this->ratio()
		];
	}

	/**
	 * Aspect ratio for SVGs without their own size,
	 * which the preview cannot fit by a natural size
	 * @since 5.6.2
	 */
	public function ratio(): float|null
	{
		if ($this->file->extension() !== 'svg') {
			return null;
		}

		$svg = Svg::from($this->file->root());

		if ($svg->hasNaturalSize() === true) {
			return null;
		}

		// without any size information, fall back to a square
		$ratio = $svg->ratio();

		if ($ratio === 0.0) {
			return 1.0;
		}

		return $ratio;
	}
}
