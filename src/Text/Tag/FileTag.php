<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\Html;
use Kirby\Text\KirbyTag;

/**
 * Renders the `(file: …)` tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class FileTag extends KirbyTag
{
	public function __construct(
		public string|null $class = null,
		public string|null $download = null,
		public string|null $rel = null,
		public string|null $target = null,
		public string|null $text = null,
		public string|null $title = null
	) {
	}

	public function render(): string
	{
		$file = $this->file($this->value);

		if ($file === null) {
			return $this->text ?? $this->value;
		}

		// use filename if the text is empty and make sure to
		// ignore markdown italic underscores in filenames
		if ($this->text === null || $this->text === '') {
			$this->text = str_replace('_', '\_', $file->filename());
		}

		return Html::a($file->url(), $this->text, [
			'class'    => $this->class,
			'download' => $this->download !== 'false',
			'rel'      => $this->rel,
			'target'   => $this->target,
			'title'    => $this->title,
		]);
	}
}
