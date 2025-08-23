<?php

namespace Kirby\Panel\Ui\FilePreview;

use Kirby\Cms\File;
use Kirby\Panel\Ui\FilePreview;
use Kirby\Toolkit\I18n;
use Override;

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

	#[Override]
	public static function accepts(File $file): bool
	{
		return $file->type() === 'image';
	}

	#[Override]
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

	#[Override]
	public function props(): array
	{
		return [
			...parent::props(),
			'focusable' => $this->file->panel()->isFocusable()
		];
	}
}
