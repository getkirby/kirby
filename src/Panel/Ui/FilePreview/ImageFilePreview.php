<?php

namespace Kirby\Panel\Ui\FilePreview;

use Kirby\Cms\File;
use Kirby\Image\Dimensions;
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
		$details    = parent::details();
		$dimensions = $this->file->dimensions();

		if ($dimensions instanceof Dimensions) {
			$details[] = [
				'title' => I18n::translate('dimensions'),
				'text'  => $dimensions . ' ' . I18n::translate('pixel')
			];

			$details[] = [
				'title' => I18n::translate('orientation'),
				'text'  => I18n::translate('orientation.' . $dimensions->orientation())
			];
		}

		return $details;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'focusable' => $this->file->panel()->isFocusable()
		];
	}
}
