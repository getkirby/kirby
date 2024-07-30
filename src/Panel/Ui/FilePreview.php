<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\FilePreviews\FileDefaultPreview;
use Kirby\Toolkit\I18n;

/**
 * Defines a component that implements a file preview
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
abstract class FilePreview extends Component
{
	public string $component = 'k-file-default-preview';

	public function __construct(
		public File $file
	) {
	}

	/**
	 * Returns true if this class should
	 * handle the preview of this file
	 */
	abstract public static function accepts(File $file): bool;

	/**
	 * Returns detail information about the file
	 */
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
				'text'  => '/' . $this->file->id(),
				'link'  => $this->file->previewUrl()
			],
			[
				'title' => I18n::translate('size'),
				'text'  => $this->file->niceSize()
			],
		];
	}

	/**
	 * Returns a file preview instance by going through all
	 * available handler classes and finding the first that
	 * accepts the file
	 */
	final public static function factory(File $file): static
	{
		// get file preview classes providers from plugins
		$handlers = App::instance()->extensions('filePreviews');

		foreach ($handlers as $handler) {
			if (is_subclass_of($handler, FilePreview::class) === false) {
				throw new InvalidArgumentException('File preview handler "' . $handler . '" must extend Kirby\Panel\Ui\FilePreview');
			}

			if ($handler::accepts($file) === true) {
				return new $handler($file);
			}
		}

		return new FileDefaultPreview($file);
	}

	public function props(): array
	{
		return [
			'details' => $this->details(),
			'url'     => $this->file->previewUrl()
		];
	}
}
