<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Toolkit\I18n;

/**
 * Component that implements a file preview
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class FilePreview extends Component
{
	public string $component = 'k-file-default-preview';

	public function __construct(
		public File $file
	) {
	}

	public static function accepts(File $file): bool
	{
		return true;
	}

	public function details(): array
	{
		return [
			[
				'title' => I18n::translate('template'),
				'text'  => $this->file->template() ?? '—'
			],
			[
				'title' => I18n::translate('mime'),
				'text'  => $this->file->mime()
			],
			[
				'title' => I18n::translate('url'),
				'text'  => $this->file->id(),
				'link'  => $this->file->previewUrl()
			],
			[
				'title' => I18n::translate('size'),
				'text'  => $this->file->niceSize()
			],
		];
	}

	final public static function factory(File $file): static
	{
		// get file preview classes providers from plugins
		$handlers = App::instance()->extensions('filePreviews');

		foreach ($handlers as $handler) {
			if ($handler::accepts($file) === true) {
				return new $handler($file);
			}
		}

		return new static($file);
	}

	public function image(): array|null
	{
		return $this->file->panel()->image([
			'back'  => 'transparent',
			'ratio' => '1/1'
		], 'cards');
	}

	public function props(): array
	{
		return [
			'details' => $this->details(),
			'image'   => $this->image(),
			'url'     => $this->file->previewUrl()
		];
	}
}
