<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Text\KirbyTag
 */
class KirbyTagTest extends TestCase
{
	public function setUp(): void
	{
		KirbyTag::$types = [
			'test' => [
				'attr' => ['a', 'b'],
				'html' => fn ($tag) => 'test: ' . $tag->value . '-' . $tag->a . '-' . $tag->b
			],
			'noHtml' => [
				'attr' => ['a', 'b']
			],
			'invalidHtml' => [
				'attr' => ['a', 'b'],
				'html' => 'some string'
			],
			'file' => [
				'attr' => ['a'],
				'html' => 'some string'
			],
		];
	}

	public function tearDown(): void
	{
		KirbyTag::$aliases = [];
		KirbyTag::$types = [];
		App::destroy();
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct()
	{
		KirbyTag::$aliases = [];
		$tag = KirbyTag::parse('test: foo a: attrA b: attrB c: attrC');
		$this->assertSame('test', $tag->type);
		$this->assertSame('foo', $tag->test);
		$this->assertSame(['a' => 'attrA', 'b' => 'attrB c: attrC'], $tag->attrs);
		$this->assertSame([], $tag->data);
		$this->assertSame([], $tag->options);
		$this->assertSame('foo', $tag->value);
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstructAliasedTagType()
	{
		KirbyTag::$aliases = ['foo' => 'test'];
		$tag = KirbyTag::parse('foo: bar');
		$this->assertSame('test', $tag->type);
		$this->assertSame('bar', $tag->test);
	}


	/**
	 * @covers ::__construct
	 */
	public function testConstructMissingTagType()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Undefined tag type: invalid');
		KirbyTag::parse('invalid: test');
	}

	/**
	 * @covers ::__call
	 */
	public function test__call()
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$data = [
			'a' => 'dataA',
			'c' => 'dataC'
		];

		$tag = new KirbyTag('test', 'test value', $attr, $data);

		$this->assertSame('dataA', $tag->a());
		$this->assertSame('attrB', $tag->b());
		$this->assertSame('dataC', $tag->c());
	}

	/**
	 * @covers ::__callStatic
	 */
	public function test__callStatic()
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$result = KirbyTag::test('test value', $attr);
		$this->assertSame('test: test value-attrA-attrB', $result);
	}

	/**
	 * @covers ::__get
	 * @covers ::attr
	 */
	public function testAttr()
	{
		$tag = new KirbyTag('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);

		// class properties
		$this->assertSame('attrA', $tag->a);
		$this->assertSame('attrB', $tag->b);

		// attr helper
		$this->assertSame('attrA', $tag->attr('a', 'fallback'));
		$this->assertSame('attrB', $tag->attr('b', 'fallback'));
	}

	/**
	 * @covers ::__get
	 * @covers ::attr
	 */
	public function testAttrFallback()
	{
		$tag = new KirbyTag('test', 'test value', [
			'a' => 'attrA'
		]);

		$this->assertNull($tag->b);
		$this->assertSame('fallback', $tag->attr('b', 'fallback'));
	}

	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$result = KirbyTag::factory('test', 'test value', $attr);
		$this->assertSame('test: test value-attrA-attrB', $result);
	}

	/**
	 * @covers ::option
	 */
	public function testOption()
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$data = [
			'a' => 'dataA',
			'b' => 'dataB'
		];

		$options = [
			'a' => 'optionA',
			'b' => 'optionB'
		];

		$tag = new KirbyTag('test', 'test value', $attr, $data, $options);

		$this->assertSame('optionA', $tag->option('a'));
		$this->assertSame('optionB', $tag->option('b'));
		$this->assertSame('optionC', $tag->option('c', 'optionC'));
	}

	public function testWithParent()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							],
							[
								'filename' => 'b.jpg'
							],
							[
								'filename' => 'c.jpg'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$image = $page->image('b.jpg');
		$expected = '<figure><img alt="" src="/media/pages/a/' . $image->mediaHash() . '/b.jpg"></figure>';

		$this->assertSame($expected, $app->kirbytag('image', 'b.jpg', [], [
			'parent' => $page,
		]));
	}

	/**
	 * @covers ::parse
	 * @dataProvider parseProvider
	 */
	public function testParse(string $string, array $data, array $options, array $expected)
	{
		$tag = KirbyTag::parse($string, $data, $options);
		foreach ($expected as $key => $value) {
			$this->assertSame($value, $tag->$key);
		}
	}

	public static function parseProvider(): array
	{
		return [
			[
				'(test: test value)',
				['some' => 'data'],
				['some' => 'options'],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'data'    => ['some' => 'data'],
					'options' => ['some' => 'options'],
					'attrs'   => []
				]
			],
			[
				'test: test value',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => []
				]
			],
			[
				'test:',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => '',
					'attrs'   => []
				]
			],
			[
				'test: ',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => '',
					'attrs'   => []
				]
			],
			[
				'test: test value a: attrA b: attrB',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test:test value a:attrA b:attrB',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA b:',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => ''
					]
				]
			],
			[
				'test: test value a: attrA b: ',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => ''
					]
				]
			],
			[
				'test: test value a: attrA b: attrB ',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA c: attrC b: attrB',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA c: attrC',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA b: attrB c: attrC',
				[],
				[],
				[
					'type'    => 'test',
					'value'   => 'test value',
					'attrs'   => [
						'a' => 'attrA',
						'b' => 'attrB c: attrC'
					]
				]
			],
			[
				'file: file://abc a: attrA b: attrB c: attrC',
				[],
				[],
				[
					'type'    => 'file',
					'value'   => 'file://abc',
					'attrs'   => [
						'a' => 'attrA b: attrB c: attrC'
					]
				]
			],
		];
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		$tag = new KirbyTag('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$this->assertSame('test: test value-attrA-attrB', $tag->render());

		$tag = new KirbyTag('test', '', [
			'a' => 'attrA'
		]);
		$this->assertSame('test: -attrA-', $tag->render());
	}

	/**
	 * @covers ::render
	 */
	public function testRenderNoHtml()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Invalid tag render function in tag: noHtml');

		$tag = new KirbyTag('noHtml', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$tag->render();
	}

	/**
	 * @covers ::render
	 */
	public function testRenderInvalidHtml()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Invalid tag render function in tag: invalidHtml');

		$tag = new KirbyTag('invalidHtml', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$tag->render();
	}

	/**
	 * @covers ::type
	 */
	public function testType()
	{
		$tag = new KirbyTag('test', 'test value');
		$this->assertSame('test', $tag->type());
	}
}
