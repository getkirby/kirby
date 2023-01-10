<?php

namespace Kirby\Text;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;

class KirbyTagsTest extends TestCase
{
	protected $app;
	protected $tmp;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp = __DIR__ . '/tmp'
			]
		]);

		Dir::make($this->tmp);
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);
	}

	public function dataProvider()
	{
		$tests = [];

		foreach (Dir::read($root = __DIR__ . '/fixtures/kirbytext') as $dir) {
			$kirbytext = F::read($root . '/' . $dir . '/test.txt');
			$expected  = F::read($root . '/' . $dir . '/expected.html');

			$tests[] = [trim($kirbytext), trim($expected)];
		}

		return $tests;
	}

	public function testParse()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => function () {
					return 'test';
				}
			]
		];

		$this->assertSame('test', KirbyTags::parse('(test: foo)'));
		$this->assertSame('test', KirbyTags::parse('(Test: foo)'));
		$this->assertSame('test', KirbyTags::parse('(TEST: foo)'));
		$this->assertSame('test', KirbyTags::parse('(tEsT: foo)'));
	}

	public function testParseWithValue()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => function ($tag) {
					return $tag->value;
				}
			]
		];

		$this->assertSame('foo', KirbyTags::parse('(test: foo)'));
		$this->assertSame('foo', KirbyTags::parse('(Test: foo)'));
		$this->assertSame('foo', KirbyTags::parse('(TEST: foo)'));
	}

	public function testParseWithAttribute()
	{
		KirbyTag::$types = [
			'test' => [
				'attr' => ['a'],
				'html' => function ($tag) {
					return $tag->value . '|' . $tag->a;
				}
			]
		];

		$this->assertSame('foo|bar', KirbyTags::parse('(test: foo a: bar)'));
		$this->assertSame('foo|bar', KirbyTags::parse('(Test: foo A: bar)'));
		$this->assertSame('foo|bar', KirbyTags::parse('(TEST: foo a: bar)'));
	}

	public function testParseWithException()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => function () {
					throw new Exception('Just for fun');
				}
			],
			'invalidargument' => [
				'html' => function () {
					throw new InvalidArgumentException('Just for fun');
				}
			],
			'undefined' => [
				'html' => function () {
					throw new InvalidArgumentException('Undefined tag type: undefined');
				}
			]
		];

		$this->assertSame('(test: foo)', KirbyTags::parse('(test: foo)'));
		$this->assertSame('(invalidargument: foo)', KirbyTags::parse('(invalidargument: foo)'));
		$this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)'));
	}

	public function testParseWithExceptionDebug1()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('Just for fun');

		KirbyTag::$types = [
			'test' => [
				'html' => function () {
					throw new Exception('Just for fun');
				}
			]
		];

		KirbyTags::parse('(test: foo)', [], ['debug' => true]);
	}

	public function testParseWithExceptionDebug2()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Just for fun');

		KirbyTag::$types = [
			'invalidargument' => [
				'html' => function () {
					throw new InvalidArgumentException('Just for fun');
				}
			]
		];

		KirbyTags::parse('(invalidargument: foo)', [], ['debug' => true]);
	}

	public function testParseWithExceptionDebug3()
	{
		KirbyTag::$types = [
			'undefined' => [
				'html' => function () {
					throw new InvalidArgumentException('Undefined tag type: undefined');
				}
			]
		];

		$this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)', [], ['debug' => true]));
	}

	public function testParseWithBrackets()
	{
		KirbyTag::$types = [
			'test' => [
				'attr' => ['a'],
				'html' => function ($tag) {
					$value = $tag->value;

					if (empty($tag->a) === false) {
						$value .= ' - ' . $tag->a;
					}

					return $value;
				}
			]
		];

		$this->assertSame('foo(bar)', KirbyTags::parse('(test: foo(bar))'));
		$this->assertSame('foo(bar) - hello(world)', KirbyTags::parse('(test: foo(bar) a: hello(world))'));
		$this->assertSame('foo(bar) hello', KirbyTags::parse('(test: foo(bar) hello)'));
		$this->assertSame('foo(bar hello(world))', KirbyTags::parse('(test: foo(bar hello(world)))'));
		$this->assertSame('foo - (bar)', KirbyTags::parse('(test: foo a: (bar))'));
		$this->assertSame('(bar)', KirbyTags::parse('(test: (bar))'));
		// will not parse if brackets don't match
		$this->assertSame('(test: foo (bar)', KirbyTags::parse('(test: foo (bar)'));
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testWithMarkdown($kirbytext, $expected)
	{
		$kirby = $this->app->clone([
			'options' => [
				'markdown' => [
					'extra' => false
				]
			]
		]);

		$this->assertEquals($expected, $kirby->kirbytext($kirbytext));
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testWithMarkdownExtra($kirbytext, $expected)
	{
		$kirby = $this->app->clone([
			'options' => [
				'markdown' => [
					'extra' => true
				]
			]
		]);

		$this->assertEquals($expected, $kirby->kirbytext($kirbytext));
	}

	public function testImageWithoutFigure()
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'image' => [
						'figure' => false
					]
				]
			]
		]);

		$expected = '<img alt="" src="https://test.com/something.jpg">';

		$this->assertEquals($expected, $kirby->kirbytext('(image: https://test.com/something.jpg)'));
	}

	public function testImageWithCaption()
	{
		$kirby    = $this->app->clone();
		$expected = '<figure><img alt="" src="/myimage.jpg"><figcaption>This is an <em>awesome</em> image and this a <a href="">link</a></figcaption></figure>';

		$this->assertEquals($expected, $kirby->kirbytext('(image: myimage.jpg caption: This is an *awesome* image and this a <a href="">link</a>)'));
	}

	public function testImageWithFileLink()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(image: image.jpg link: document.pdf)'
						],
						'files' => [
							[
								'filename' => 'image.jpg',
							],
							[
								'filename' => 'document.pdf',
							]
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');
		$doc   = $page->file('document.pdf');

		$expected = '<figure><a href="' . $doc->url() . '"><img alt="" src="' . $image->url() . '"></a></figure>';

		$this->assertEquals($expected, $page->text()->kt()->value());
	}

	public function testImageWithFileUUID()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(image: file://image-uuid)',
							'uuid' => 'page-uuid' // this is just to make sure that the test doesn't try to create a content file for this page with a generated UUID
						],
						'files' => [
							[
								'filename' => 'image.jpg',
								'content' => ['uuid' => 'image-uuid']
							]
						]
					]
				]
			]
		]);

		$page  = $kirby->page('a');
		$image = $page->file('image.jpg');

		$expected = '<figure><img alt="" src="' . $image->url() . '"></figure>';

		$this->assertEquals($expected, $page->text()->kt()->value());
	}

	public function testFile()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: a.jpg)'
						],
						'files' => [
							[
								'filename' => 'a.jpg',
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$expected = '<p><a download href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertEquals($expected, $page->text()->kt()->value());
	}

	public function testFileWithUUID()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: file://file-a)',
							'uuid' => 'page-uuid' // this is just to make sure that the test doesn't try to create a content file for this page with a generated UUID
						],
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => ['uuid' => 'file-a']
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$expected = '<p><a download href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertEquals($expected, $page->text()->kt()->value());
	}

	public function testFileWithDisabledDownloadOption()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: a.jpg download: false)'
						],
						'files' => [
							[
								'filename' => 'a.jpg',
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$expected = '<p><a href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertEquals($expected, $page->text()->kt()->value());
	}

	public function testFileWithinFile()
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => [
									'caption' => '(file: b.jpg)'
								]
							],
							[
								'filename' => 'b.jpg'
							]
						]
					]
				]
			]
		]);

		$a = $kirby->file('a/a.jpg');
		$b = $kirby->file('a/b.jpg');
		$expected = '<p><a download href="' . $b->url() . '">b.jpg</a></p>';

		$this->assertEquals($expected, $a->caption()->kt()->value());
	}

	public function testLinkWithLangAttribute()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertEquals('<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>', $app->kirbytags('(link: a lang: en)'));
		$this->assertEquals('<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>', $app->kirbytags('(link: a lang: de)'));
	}

	public function testLinkWithHash()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$this->assertEquals('<a href="https://getkirby.com/en/a">getkirby.com/en/a</a>', $app->kirbytags('(link: a)'));
		$this->assertEquals('<a href="https://getkirby.com/de/a">getkirby.com/de/a</a>', $app->kirbytags('(link: a lang: de)'));
		$this->assertEquals('<a href="https://getkirby.com/en/a#anchor">getkirby.com/en/a</a>', $app->kirbytags('(link: a#anchor lang: en)'));
		$this->assertEquals('<a href="https://getkirby.com/de/a#anchor">getkirby.com/de/a</a>', $app->kirbytags('(link: a#anchor lang: de)'));
	}

	public function testLinkWithUuid()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'page-uuid'],
						'files'   => [
							[
								'filename' => 'foo.jpg',
								'content' => ['uuid' => 'file-uuid'],
							]
						]
					]
				]
			]
		]);

		$result = $app->kirbytags('(link: page://page-uuid)');
		$this->assertEquals('<a href="https://getkirby.com/a">getkirby.com/a</a>', $result);

		$result = $app->kirbytags('(link: file://file-uuid text: file)');
		$this->assertEquals('<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>', $result);
	}

	public function testLinkWithUuidAndLang()
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true,
					'locale'  => 'en_US',
					'url'     => '/',
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
					'locale'  => 'de_DE',
					'url'     => '/de',
				],
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'a',
						'files' => [
							[
								'filename'     => 'foo.jpg',
								'translations' => [
									[
										'code' => 'en',
										'content' => ['uuid' => 'file-uuid']
									],
									[
										'code' => 'de',
										'content' => []
									]
								]
							]
						],
						'translations' => [
							[
								'code' => 'en',
								'content' => ['uuid' => 'page-uuid']
							],
							[
								'code' => 'de',
								'content' => ['slug' => 'ae']
							]
						]
					]
				]
			]
		]);

		$result = $app->kirbytags('(link: page://page-uuid lang: de)');
		$this->assertEquals('<a href="https://getkirby.com/de/ae">getkirby.com/de/ae</a>', $result);

		$result = $app->kirbytags('(link: file://file-uuid text: file lang: de)');
		$this->assertEquals('<a href="' . $app->file('a/foo.jpg')->url() . '">file</a>', $result);
	}

	public function testHooks()
	{
		$app = $this->app->clone([
			'hooks' => [
				'kirbytags:before' => function ($text, $data, $options) {
					return 'before';
				},
			]
		]);

		$this->assertEquals('before', $app->kirbytags('test'));

		$app = $app->clone([
			'hooks' => [
				'kirbytags:after' => function ($text, $data, $options) {
					return 'after';
				},
			]
		]);

		$this->assertEquals('after', $app->kirbytags('test'));
	}

	public function testVideoLocal()
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

	public function testVideoInlineAttrs()
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

		$expected = '<figure class="video-class" style="border: none"><video autoplay height="350" loop muted playsinline poster="' . $image->url() . '" preload="auto" width="500"><source src="' . $video->url() . '" type="video/mp4"></video><figcaption>Lorem ipsum</figcaption></figure>';
		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testVideoPredefinedAttrs()
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

	public function testVideoAutoplayRelatedAttrs()
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

	public function testVideoAutoplayAttrsOverride()
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

	public function testVideoOptions()
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

	public function testVideoRemote()
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

	public function globalOptionsProvider(): array
	{
		return [
			[
				'(image: image.jpg link: https://getkirby.com/)',
				'<figure><a href="https://getkirby.com/" rel="nofollow"><img alt="" class="image-class" src="/image.jpg"></a></figure>'
			],
			[
				'(link: http://wikipedia.org text: Wikipedia)',
				'<p><a class="link-class" href="http://wikipedia.org" rel="noopener noreferrer" target="_blank">Wikipedia</a></p>'
			],
			[
				'(tel: +49123456789)',
				'<p><a class="phone" href="tel:+49123456789">+49123456789</a></p>'
			],
			[
				'(twitter: @getkirby)',
				'<p><a href="https://twitter.com/getkirby" rel="nofollow" target="_blank">@getkirby</a></p>'
			],
			[
				'(video: https://www.youtube.com/watch?v=VhP7ZzZysQg)',
				'<figure class="video-class"><iframe allow="fullscreen" allowfullscreen src="https://www.youtube.com/embed/VhP7ZzZysQg"></iframe></figure>'
			]
		];
	}

	/**
	 * @dataProvider globalOptionsProvider
	 */
	public function testGlobalOptions($kirbytext, $expected)
	{
		$kirby = $this->app->clone([
			'options' => [
				'kirbytext' => [
					'image' => [
						'rel' => 'nofollow',
						'imgclass' => 'image-class'
					],
					'link' => [
						'class' => 'link-class',
						'target' => '_blank'
					],
					'tel' => [
						'class' => 'phone'
					],
					'twitter' => [
						'rel' => 'nofollow',
						'target' => '_blank',
					],
					'video' => [
						'class' => 'video-class',
					]
				]
			]
		]);

		$this->assertSame($expected, $kirby->kirbytext($kirbytext));
	}
}
