<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\File;
use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * Renders the `(image: …)` tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class ImageTag extends KirbyTag
{
	protected File|null $file = null;

	public function __construct(
		public string|null $alt = null,
		public string|array|null $caption = null,
		public string|null $class = null,
		public string|int|null $height = null,
		public string|null $imgclass = null,
		public string|null $link = null,
		public string|null $linkclass = null,
		public string|null $rel = null,
		public string|array|null $srcset = null,
		public string|null $target = null,
		public string|null $title = null,
		public string|int|null $width = null
	) {
	}

	protected function alt(): string
	{
		return $this->alt ?? $this->file()?->alt()->value() ?? '';
	}

	protected function caption(): array|null
	{
		$caption = $this->caption ?? $this->file()?->caption()->value();

		if (!$caption) {
			return null;
		}

		// render KirbyText in caption
		$options = ['markdown' => ['inline' => true]];
		return [$this->kirby()->kirbytext($caption, $options)];
	}

	/**
	 * Resolves the tag's own file from its value (memoized); when a
	 * path is passed, looks up that file through the base finder
	 */
	public function file(string|null $path = null): File|null
	{
		if ($path !== null) {
			return parent::file($path);
		}

		return $this->file ??= $this->value !== null ? parent::file($this->value) : null;
	}

	protected function height(): string|int|null
	{
		return match ($this->height) {
			'auto'  => $this->file()?->height(),
			default => $this->height
		};
	}

	protected function link(): string|null
	{
		if ($this->link === null || $this->link === '') {
			return null;
		}

		$link   = $this->file($this->link)?->url();
		$link ??= match ($this->link) {
			'self'  => $this->src(),
			default => $this->link
		};

		return $link;
	}

	public function render(): string
	{
		$html = Html::img($this->src(), [
			'srcset' => $this->srcset(),
			'width'  => $this->width(),
			'height' => $this->height(),
			'class'  => $this->imgclass,
			'title'  => $this->title(),
			'alt'    => $this->alt()
		]);

		// if link exists, wrap in `<a>` tag
		if ($link = $this->link()) {
			$html = Html::a($link, [$html], [
				'rel'    => $this->rel,
				'class'  => $this->linkclass,
				'target' => $this->target
			]);
		}

		if ($this->kirby()->option('kirbytext.image.figure', true) === true) {
			$html = Html::figure([$html], $this->caption(), [
				'class' => $this->class
			]);
		}

		return $html;
	}

	protected function src(): string
	{
		return $this->file()?->url() ?? Url::to($this->value);
	}

	protected function srcset(): string|array|null
	{
		if (!$srcset = $this->srcset) {
			return null;
		}

		if ($this->file() === null) {
			return $srcset;
		}

		$srcset = Str::split($srcset);
		$srcset = match (count($srcset) > 1) {
			// comma-separated list of sizes
			true    => A::map($srcset, fn ($size) => (int)trim($size)),
			// srcset config name
			default => $srcset[0]
		};

		return $this->file()->srcset($srcset);
	}

	protected function title(): string|null
	{
		return $this->title ?? $this->file()?->title()->value();
	}

	protected function width(): string|int|null
	{
		return match ($this->width) {
			'auto'  => $this->file()?->width(),
			default => $this->width
		};
	}
}
