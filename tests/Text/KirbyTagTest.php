<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(KirbyTag::class)]
class KirbyTagTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Text.KirbyTag';

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

	public function testConstruct(): void
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

	public function testConstructAliasedTagType(): void
	{
		KirbyTag::$aliases = ['foo' => 'test'];
		$tag = KirbyTag::parse('foo: bar');
		$this->assertSame('test', $tag->type);
		$this->assertSame('bar', $tag->test);
	}

	public function testConstructMissingTagType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Undefined tag type: invalid');
		KirbyTag::parse('invalid: test');
	}

	public function test__call(): void
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

	public function test__callStatic(): void
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$result = KirbyTag::test('test value', $attr);
		$this->assertSame('test: test value-attrA-attrB', $result);
	}

	public function testAttr(): void
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

	public function testAttrFallback(): void
	{
		$tag = new KirbyTag('test', 'test value', [
			'a' => 'attrA'
		]);

		$this->assertNull($tag->b);
		$this->assertSame('fallback', $tag->attr('b', 'fallback'));
	}

	public function testFactory(): void
	{
		$attr = [
			'a' => 'attrA',
			'b' => 'attrB'
		];

		$result = KirbyTag::factory('test', 'test value', $attr);
		$this->assertSame('test: test value-attrA-attrB', $result);
	}

	public function testFile(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$file = $page->file('a.jpg');
		$tag  = new KirbyTag('image', 'foo');
		$this->assertSame($file, $tag->file('a/a.jpg'));
	}

	public function testFileInParent(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$file = $page->file('a.jpg');
		$tag  = new KirbyTag('image', 'foo', [], [
			'parent' => $page,
		]);
		$this->assertSame($file, $tag->file('a.jpg'));
	}

	public function testFileInFileParent(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$file = $page->file('a.jpg');
		$tag  = new KirbyTag('image', 'foo', [], [
			'parent' => $file,
		]);
		$this->assertSame($file, $tag->file('a.jpg'));
	}

	public function testFileFromUuid(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => ['uuid' => 'image-uuid']
							]
						]
					]
				]
			]
		]);

		$page = $app->page('a');
		$file = $page->file('a.jpg');
		$tag  = new KirbyTag('image', 'foo');
		$this->assertSame($file, $tag->file('file://image-uuid'));

		// with parent
		$tag = new KirbyTag('image', 'foo', [], [
			'parent' => $page,
		]);
		$this->assertSame($file, $tag->file('file://image-uuid'));
	}

	public function testKirby(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			]
		]);

		$tag = new KirbyTag('image', 'b.jpg');
		$this->assertSame($app, $tag->kirby());
	}

	public function testOption(): void
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

	public function testParent(): void
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
							]
						]
					]
				]
			]
		]);

		$page  = $app->page('a');
		$tag   = new KirbyTag('image', 'b.jpg', [], [
			'parent' => $page,
		]);

		$this->assertSame($page, $tag->parent());
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

	#[DataProvider('parseProvider')]
	public function testParse(
		string $string,
		array $data,
		array $options,
		array $expected
	): void {
		$tag = KirbyTag::parse($string, $data, $options);
		foreach ($expected as $key => $value) {
			$this->assertSame($value, $tag->$key);
		}
	}

	public function testRender(): void
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

	public function testRenderNoHtml(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Invalid tag render function in tag: noHtml');

		$tag = new KirbyTag('noHtml', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$tag->render();
	}

	public function testRenderInvalidHtml(): void
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('Invalid tag render function in tag: invalidHtml');

		$tag = new KirbyTag('invalidHtml', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$tag->render();
	}

	public function testType(): void
	{
		$tag = new KirbyTag('test', 'test value');
		$this->assertSame('test', $tag->type());
	}
}
