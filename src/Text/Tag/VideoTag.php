<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\Html;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\Str;

/**
 * Renders the `(video: …)` tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class VideoTag extends KirbyTag
{
	public function __construct(
		public string|bool|null $autoplay = null,
		public string|array|null $caption = null,
		public string|bool|null $controls = null,
		public string|null $class = 'video',
		public string|bool|null $disablepictureinpicture = null,
		public string|int|null $height = null,
		public string|bool|null $loop = null,
		public string|bool|null $muted = null,
		public string|bool|null $playsinline = null,
		public string|null $poster = null,
		public string|null $preload = null,
		public string|int|null $width = null
	) {
	}

	protected function attributes(): array
	{
		$attrs = [
			'height' => $this->height,
			'width'  => $this->width
		];

		// iframe (provider) videos only support these attributes
		if ($this->isProviderVideo() === true) {
			return $attrs;
		}

		// convert tag attributes to supported formats (bool, string)
		// to output correct html attributes
		//
		// for ex: `autoplay` will not work if `false` is a string
		// instead of a boolean
		return [
			...$attrs,
			'autoplay'                => $autoplay = Str::toType($this->autoplay, 'bool'),
			'controls'                => Str::toType($this->controls ?? true, 'bool'),
			'disablepictureinpicture' => Str::toType($this->disablepictureinpicture ?? false, 'bool'),
			'loop'                    => Str::toType($this->loop, 'bool'),
			'muted'                   => Str::toType($this->muted ?? $autoplay, 'bool'),
			'playsinline'             => Str::toType($this->playsinline ?? $autoplay, 'bool'),
			'poster'                  => $this->poster(),
			'preload'                 => $this->preload
		];
	}

	protected function isLocalVideo(): bool
	{
		return
			Str::startsWith($this->value, 'http://') !== true &&
			Str::startsWith($this->value, 'https://') !== true;
	}

	protected function isProviderVideo(): bool
	{
		return
			$this->isLocalVideo() === false &&
			(
				Str::contains($this->value, 'youtu', true) === true ||
				Str::contains($this->value, 'vimeo', true) === true
			);
	}

	protected function poster(): string|null
	{
		// resolve a local file poster to its URL
		if (
			$this->poster !== null &&
			$this->poster !== '' &&
			Str::startsWith($this->poster, 'http://') !== true &&
			Str::startsWith($this->poster, 'https://') !== true
		) {
			return $this->file($this->poster)?->url() ?? $this->poster;
		}

		return $this->poster;
	}

	public function render(): string
	{
		return Html::figure([$this->video() ?? ''], $this->caption, [
			'class' => $this->class
		]);
	}

	protected function video(): string|null
	{
		// remote / provider video (e.g. YouTube, Vimeo)
		if ($this->isLocalVideo() === false) {
			return Html::video(
				$this->value,
				$this->kirby()->option('kirbytext.video.options', []),
				$this->attributes()
			);
		}

		// local video file
		if ($file = $this->file($this->value)) {
			$source = Html::tag('source', '', [
				'src'  => $file->url(),
				'type' => $file->mime()
			]);

			return Html::tag('video', [$source], $this->attributes());
		}

		return null;
	}
}
