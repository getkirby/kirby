<?php

use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Exception\NotFoundException;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

/**
 * Default KirbyTags definition
 */
return [

	/**
	 * Date
	 */
	'date' => [
		'attr' => [],
		'html' => function (KirbyTag $tag): string {
			if (strtolower($tag->date) === 'year') {
				return date('Y');
			}

			return date($tag->date);
		}
	],

	/**
	 * Email
	 */
	'email' => [
		'attr' => [
			'class',
			'rel',
			'target',
			'text',
			'title'
		],
		'html' => function (KirbyTag $tag): string {
			return Html::email($tag->value, $tag->text, [
				'class'  => $tag->class,
				'rel'    => $tag->rel,
				'target' => $tag->target,
				'title'  => $tag->title,
			]);
		}
	],

	/**
	 * File
	 */
	'file' => [
		'attr' => [
			'class',
			'download',
			'rel',
			'target',
			'text',
			'title'
		],
		'html' => function (KirbyTag $tag): string {
			if (!$file = $tag->file($tag->value)) {
				return $tag->text ?? $tag->value;
			}

			// use filename if the text is empty and make sure to
			// ignore markdown italic underscores in filenames
			if (empty($tag->text) === true) {
				$tag->text = str_replace('_', '\_', $file->filename());
			}

			return Html::a($file->url(), $tag->text, [
				'class'    => $tag->class,
				'download' => $tag->download !== 'false',
				'rel'      => $tag->rel,
				'target'   => $tag->target,
				'title'    => $tag->title,
			]);
		}
	],

	/**
	 * Gist
	 */
	'gist' => [
		'attr' => [
			'file'
		],
		'html' => function (KirbyTag $tag): string {
			return Html::gist($tag->value, $tag->file);
		}
	],

	/**
	 * Image
	 */
	'image' => [
		'attr' => [
			'alt',
			'caption',
			'class',
			'height',
			'imgclass',
			'link',
			'linkclass',
			'rel',
			'srcset',
			'target',
			'title',
			'width'
		],
		'html' => function (KirbyTag $tag): string {
			$kirby = $tag->kirby();

			$tag->width  ??= $kirby->option('kirbytext.image.width');
			$tag->height ??= $kirby->option('kirbytext.image.height');

			if ($tag->file = $tag->file($tag->value)) {
				$tag->src       = $tag->file->url();
				$tag->alt     ??= $tag->file->alt()->or('')->value();
				$tag->title   ??= $tag->file->title()->value();
				$tag->caption ??= $tag->file->caption()->value();

				if ($srcset = $tag->srcset) {
					$srcset = Str::split($srcset);
					$srcset = match (count($srcset) > 1) {
						// comma-separated list of sizes
						true => A::map($srcset, fn ($size) => (int)trim($size)),
						// srcset config name
						default => $srcset[0]
					};

					$tag->srcset = $tag->file->srcset($srcset);
				}

				if ($tag->width === 'auto') {
					$tag->width = $tag->file->width();
				}
				if ($tag->height === 'auto') {
					$tag->height = $tag->file->height();
				}
			} else {
				$tag->src = Url::to($tag->value);
			}

			$link = function ($img) use ($tag) {
				if (empty($tag->link) === true) {
					return $img;
				}

				$link   = $tag->file($tag->link)?->url();
				$link ??= $tag->link === 'self' ? $tag->src : $tag->link;

				return Html::a($link, [$img], [
					'rel'    => $tag->rel,
					'class'  => $tag->linkclass,
					'target' => $tag->target
				]);
			};

			$image = Html::img($tag->src, [
				'srcset' => $tag->srcset,
				'width'  => $tag->width,
				'height' => $tag->height,
				'class'  => $tag->imgclass,
				'title'  => $tag->title,
				'alt'    => $tag->alt ?? ''
			]);

			if ($kirby->option('kirbytext.image.figure', true) === false) {
				return $link($image);
			}

			// render KirbyText in caption
			if ($tag->caption) {
				$options = ['markdown' => ['inline' => true]];
				$caption = $kirby->kirbytext($tag->caption, $options);
				$tag->caption = [$caption];
			}

			return Html::figure([$link($image)], $tag->caption, [
				'class' => $tag->class
			]);
		}
	],

	/**
	 * Link
	 */
	'link' => [
		'attr' => [
			'class',
			'lang',
			'rel',
			'role',
			'target',
			'title',
			'text',
		],
		'html' => function (KirbyTag $tag): string {
			if (empty($tag->lang) === false) {
				$tag->value = Url::to($tag->value, $tag->lang);
			}

			// if value is a UUID, resolve to page/file model
			// and use the URL as value
			if (
				Uuid::is($tag->value, 'page') === true ||
				Uuid::is($tag->value, 'file') === true
			) {
				$tag->value = Uuid::for($tag->value)->model()?->url();
			}

			// if url is empty, throw exception or link to the error page
			if ($tag->value === null) {
				if ($tag->kirby()->option('debug', false) === true) {
					if (empty($tag->text) === false) {
						throw new NotFoundException('The linked page cannot be found for the link text "' . $tag->text . '"');
					} else {
						throw new NotFoundException('The linked page cannot be found');
					}
				} else {
					$tag->value = Url::to($tag->kirby()->site()->errorPageId());
				}
			}

			return Html::a($tag->value, $tag->text, [
				'rel'    => $tag->rel,
				'class'  => $tag->class,
				'role'   => $tag->role,
				'title'  => $tag->title,
				'target' => $tag->target,
			]);
		}
	],

	/**
	 * Tel
	 */
	'tel' => [
		'attr' => [
			'class',
			'rel',
			'text',
			'title'
		],
		'html' => function (KirbyTag $tag): string {
			return Html::tel($tag->value, $tag->text, [
				'class' => $tag->class,
				'rel'   => $tag->rel,
				'title' => $tag->title
			]);
		}
	],

	/**
	 * Video
	 */
	'video' => [
		'attr' => [
			'autoplay',
			'caption',
			'controls',
			'class',
			'disablepictureinpicture',
			'height',
			'loop',
			'muted',
			'playsinline',
			'poster',
			'preload',
			'style',
			'width',
		],
		'html' => function (KirbyTag $tag): string {
			// checks and gets if poster is local file
			if (
				empty($tag->poster) === false &&
				Str::startsWith($tag->poster, 'http://') !== true &&
				Str::startsWith($tag->poster, 'https://') !== true
			) {
				if ($poster = $tag->file($tag->poster)) {
					$tag->poster = $poster->url();
				}
			}

			// checks video is local or provider(remote)
			$isLocalVideo = (
				Str::startsWith($tag->value, 'http://') !== true &&
				Str::startsWith($tag->value, 'https://') !== true
			);
			$isProviderVideo = (
				$isLocalVideo === false &&
				(
					Str::contains($tag->value, 'youtu', true) === true ||
					Str::contains($tag->value, 'vimeo', true) === true
				)
			);

			// default attributes for local and remote videos
			$attrs = [
				'height' => $tag->height,
				'width'  => $tag->width
			];

			// don't use attributes that iframe doesn't support
			if ($isProviderVideo === false) {
				// convert tag attributes to supported formats (bool, string)
				// to output correct html attributes
				//
				// for ex:
				// `autoplay` will not work if `false` is a string
				// instead of a boolean
				$attrs['autoplay']    = $autoplay = Str::toType($tag->autoplay, 'bool');
				$attrs['controls']    = Str::toType($tag->controls ?? true, 'bool');
				$attrs['disablepictureinpicture'] = Str::toType($tag->disablepictureinpicture ?? false, 'bool');
				$attrs['loop']        = Str::toType($tag->loop, 'bool');
				$attrs['muted']       = Str::toType($tag->muted ?? $autoplay, 'bool');
				$attrs['playsinline'] = Str::toType($tag->playsinline ?? $autoplay, 'bool');
				$attrs['poster']      = $tag->poster;
				$attrs['preload']     = $tag->preload;
			}

			// handles local and remote video file
			if ($isLocalVideo === true) {
				// handles local video file
				if ($tag->file = $tag->file($tag->value)) {
					$source = Html::tag('source', '', [
						'src'  => $tag->file->url(),
						'type' => $tag->file->mime()
					]);
					$video = Html::tag('video', [$source], $attrs);
				}
			} else {
				$video = Html::video(
					$tag->value,
					$tag->kirby()->option('kirbytext.video.options', []),
					$attrs
				);
			}

			return Html::figure([$video ?? ''], $tag->caption, [
				'class' => $tag->class ?? 'video',
				'style' => $tag->style
			]);
		}
	],

];
