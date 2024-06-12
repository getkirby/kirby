<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class VideoKirbyTagTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Text.VideoKirbyTag';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testLocal()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4)'
						],
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$image = $page->file('sample.mp4');

		$expected = '<figure class="video"><video controls><source src="' . $image->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testInlineAttrs()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4
                                autoplay: true
                                caption: Lorem ipsum
                                controls: false
                                class: video-class
                                disablepictureinpicture: true
                                height: 350
                                loop: true
                                muted: true
                                playsinline: true
                                poster: sample.jpg
                                preload: auto
                                style: border: none
                                width: 500)'
						],
						'files' => [
							['filename' => 'sample.mp4'],
							['filename' => 'sample.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');

		$image = $page->file('sample.jpg');
		$video = $page->file('sample.mp4');

		$expected = '<figure class="video-class" style="border: none"><video autoplay disablepictureinpicture height="350" loop muted playsinline poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testPredefinedAttrs()
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'video' => [
						'autoplay'    => true,
						'caption'     => 'Lorem ipsum',
						'controls'    => false,
						'class'       => 'video-class',
						'height'      => 350,
						'loop'        => true,
						'muted'       => true,
						'playsinline' => true,
						'poster'      => 'sample.jpg',
						'preload'     => 'auto',
						'style'       => 'border: none',
						'width'       => 500
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4)'
						],
						'files' => [
							['filename' => 'sample.mp4'],
							['filename' => 'sample.jpg']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');

		$image = $page->file('sample.jpg');
		$video = $page->file('sample.mp4');

		$expected = '<figure class="video-class" style="border: none"><video autoplay height="350" loop muted playsinline poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testAutoplayRelatedAttrs()
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4 autoplay: true)'
						],
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$video = $page->file('sample.mp4');

		$expected = '<figure class="video"><video autoplay controls muted playsinline><source src="' . $video->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testAutoplayAttrsOverride()
	{
		$kirby = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: sample.mp4 autoplay: true muted: false playsinline: false)'
						],
						'files' => [
							['filename' => 'sample.mp4']
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');
		$image = $page->file('sample.mp4');

		$expected = '<figure class="video"><video autoplay controls><source src="' . $image->url() . '" type="video/mp4"></video></figure>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testOptions()
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'video' => [
						'options'  => [
							'youtube' => [
								'controls' => 0
							]
						]
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: https://www.youtube.com/watch?v=VhP7ZzZysQg)'
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');

		$expected = '<figure class="video"><iframe allow="fullscreen" allowfullscreen src="https://www.youtube.com/embed/VhP7ZzZysQg?controls=0"></iframe></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testRemote()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'text' => '(video: https://getkirby.com/sample.mp4)'
						]
					]
				]
			]
		]);

		$page  = $kirby->page('test');

		$expected = '<figure class="video"><video controls><source src="https://getkirby.com/sample.mp4" type="video/mp4"></video></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}
}
