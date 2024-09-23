<?php

namespace Kirby\Text;

use Exception;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Text\KirbyTags
 */
class KirbyTagsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Text.KirbyTags';

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

	public static function dataProvider()
	{
		$tests = [];

		foreach (Dir::read($root = static::FIXTURES . '/kirbytext') as $dir) {
			$kirbytext = F::read($root . '/' . $dir . '/test.txt');
			$expected  = F::read($root . '/' . $dir . '/expected.html');

			$tests[] = [trim($kirbytext), trim($expected)];
		}

		return $tests;
	}

	/**
	 * @covers ::parse
	 */
	public function testParse()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => fn () => 'test'
			]
		];

		$this->assertSame('test', KirbyTags::parse('(test: foo)'));
		$this->assertSame('test', KirbyTags::parse('(Test: foo)'));
		$this->assertSame('test', KirbyTags::parse('(TEST: foo)'));
		$this->assertSame('test', KirbyTags::parse('(tEsT: foo)'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithValue()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => fn ($tag) => $tag->value
			]
		];

		$this->assertSame('foo', KirbyTags::parse('(test: foo)'));
		$this->assertSame('foo', KirbyTags::parse('(Test: foo)'));
		$this->assertSame('foo', KirbyTags::parse('(TEST: foo)'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithAttribute()
	{
		KirbyTag::$types = [
			'test' => [
				'attr' => ['a'],
				'html' => fn ($tag) => $tag->value . '|' . $tag->a
			]
		];

		$this->assertSame('foo|bar', KirbyTags::parse('(test: foo a: bar)'));
		$this->assertSame('foo|bar', KirbyTags::parse('(Test: foo A: bar)'));
		$this->assertSame('foo|bar', KirbyTags::parse('(TEST: foo a: bar)'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithException()
	{
		KirbyTag::$types = [
			'test' => [
				'html' => fn () => throw new Exception('Just for fun')
			],
			'invalidargument' => [
				'html' => fn () => throw new InvalidArgumentException(
					message: 'Just for fun'
				)
			],
			'undefined' => [
				'html' => fn () => throw new InvalidArgumentException(
					message: 'Undefined tag type: undefined'
				)
			]
		];

		$this->assertSame('(test: foo)', KirbyTags::parse('(test: foo)'));
		$this->assertSame('(invalidargument: foo)', KirbyTags::parse('(invalidargument: foo)'));
		$this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithExceptionDebug1()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Just for fun');

		KirbyTag::$types = [
			'test' => [
				'html' => fn () => throw new Exception('Just for fun')
			]
		];

		KirbyTags::parse('(test: foo)', [], ['debug' => true]);
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithExceptionDebug2()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Just for fun');

		KirbyTag::$types = [
			'invalidargument' => [
				'html' => fn () => throw new InvalidArgumentException('Just for fun')
			]
		];

		KirbyTags::parse('(invalidargument: foo)', [], ['debug' => true]);
	}

	/**
	 * @covers ::parse
	 */
	public function testParseWithExceptionDebug3()
	{
		KirbyTag::$types = [
			'undefined' => [
				'html' => fn () => throw new InvalidArgumentException(
					message: 'Undefined tag type: undefined'
				)
			]
		];

		$this->assertSame('(undefined: foo)', KirbyTags::parse('(undefined: foo)', [], ['debug' => true]));
	}

	/**
	 * @covers ::parse
	 */
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
	 * @covers ::parse
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

		$this->assertSame($expected, $kirby->kirbytext($kirbytext));
	}

	/**
	 * @covers ::parse
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

		$this->assertSame($expected, $kirby->kirbytext($kirbytext));
	}

	/**
	 * @coversNothing
	 */
	public function testHooks()
	{
		$app = $this->app->clone([
			'hooks' => [
				'kirbytags:before' => fn ($text, $data, $options) => 'before'
			]
		]);

		$this->assertSame('before', $app->kirbytags('test'));

		$app = $app->clone([
			'hooks' => [
				'kirbytags:after' => fn ($text, $data, $options) => 'after'
			]
		]);

		$this->assertSame('after', $app->kirbytags('test'));
	}

	public static function globalOptionsProvider(): array
	{
		return [
			[
				'(image: image.jpg link: https://getkirby.com/)',
				'<figure><a href="https://getkirby.com/" rel="nofollow"><img alt="" class="image-class" src="/image.jpg"></a></figure>'
			],
			[
				'(link: http://wikipedia.org text: Wikipedia)',
				'<p><a class="link-class" href="http://wikipedia.org" rel="noreferrer" target="_blank">Wikipedia</a></p>'
			],
			[
				'(tel: +49123456789)',
				'<p><a class="phone" href="tel:+49123456789">+49123456789</a></p>'
			],
			[
				'(video: https://www.youtube.com/watch?v=VhP7ZzZysQg)',
				'<figure class="video-class"><iframe allow="fullscreen" allowfullscreen src="https://www.youtube.com/embed/VhP7ZzZysQg"></iframe></figure>'
			]
		];
	}

	/**
	 * @coversNothing
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
					'video' => [
						'class' => 'video-class',
					]
				]
			]
		]);

		$this->assertSame($expected, $kirby->kirbytext($kirbytext));
	}
}
