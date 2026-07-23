<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Cms\Helpers;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

class TestKirbyTag extends KirbyTag
{
	public function __construct(
		public string|null $a = null,
		public string|null $b = null
	) {
	}

	public function render(): string
	{
		return 'test: ' . $this->value . '-' . $this->a . '-' . $this->b;
	}
}

#[CoversClass(KirbyTag::class)]
class KirbyTagTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Text.KirbyTag';

	protected function setUp(): void
	{
		KirbyTag::$types = [
			'test'   => TestKirbyTag::class,
			'legacy' => [
				'attr' => ['a'],
				'html' => fn ($tag) => 'legacy'
			],
		];
	}

	protected function tearDown(): void
	{
		Helpers::$deprecations['kirbytag-option'] = true;
		KirbyTag::$aliases = [];
		KirbyTag::$types   = [];
		App::destroy();
	}

	public function testConstruct(): void
	{
		$tag = KirbyTag::parse('test: foo a: attrA b: attrB c: attrC');
		$this->assertInstanceOf(TestKirbyTag::class, $tag);
		$this->assertSame('test', $tag->type);
		$this->assertSame('foo', $tag->value);

		// only the attributes the tag type defines are applied
		$this->assertSame('attrA', $tag->a);
		$this->assertSame('attrB c: attrC', $tag->b);

		$this->assertSame(['a' => 'attrA', 'b' => 'attrB c: attrC'], $tag->attrs);
		$this->assertSame([], $tag->data);
		$this->assertSame('foo', $tag->value);
	}

	public function testConstructAliasedTagType(): void
	{
		KirbyTag::$aliases = ['foo' => 'test'];
		$tag = KirbyTag::parse('foo: bar');
		$this->assertInstanceOf(TestKirbyTag::class, $tag);
		$this->assertSame('test', $tag->type);
		$this->assertSame('bar', $tag->value);
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

		$tag = KirbyTag::factory('test', 'test value', $attr, $data);

		// data takes precedence over the property
		$this->assertSame('dataA', $tag->a());
		// falls back to the property when no data is set
		$this->assertSame('attrB', $tag->b());
		// data-only value
		$this->assertSame('dataC', $tag->c());
	}

	public function test__callStatic(): void
	{
		$result = KirbyTag::test('test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$this->assertSame('test: test value-attrA-attrB', $result);
	}

	public function testAttr(): void
	{
		$tag = KirbyTag::factory('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);

		// class properties
		$this->assertSame('attrA', $tag->a);
		$this->assertSame('attrB', $tag->b);

		// attr helper (case-insensitive)
		$this->assertSame('attrA', $tag->attr('a', 'fallback'));
		$this->assertSame('attrA', $tag->attr('A', 'fallback'));
		$this->assertSame('attrB', $tag->attr('b', 'fallback'));
	}

	public function testAttrFallback(): void
	{
		$tag = KirbyTag::factory('test', 'test value', [
			'a' => 'attrA'
		]);

		$this->assertNull($tag->b);
		$this->assertSame('fallback', $tag->attr('b', 'fallback'));
	}

	public function testAttrs(): void
	{
		$this->assertSame(['a', 'b'], TestKirbyTag::attrs());
	}

	public function testFactory(): void
	{
		// class-based type
		$tag = KirbyTag::factory('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$this->assertInstanceOf(TestKirbyTag::class, $tag);
		$this->assertSame('test: test value-attrA-attrB', $tag->render());

		// legacy array type is wrapped in LegacyKirbyTag
		$tag = KirbyTag::factory('legacy', 'test value');
		$this->assertInstanceOf(LegacyKirbyTag::class, $tag);
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
		$tag  = KirbyTag::factory('test', 'foo');
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
		$tag  = KirbyTag::factory('test', 'foo', [], [
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
		$tag  = KirbyTag::factory('test', 'foo', [], [
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
		$tag  = KirbyTag::factory('test', 'foo');
		$this->assertSame($file, $tag->file('file://image-uuid'));

		// with parent
		$tag = KirbyTag::factory('test', 'foo', [], [
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

		$tag = KirbyTag::factory('test', 'b.jpg');
		$this->assertSame($app, $tag->kirby());
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

		$page = $app->page('a');
		$tag  = KirbyTag::factory('test', 'b.jpg', [], [
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
				[
					'type'  => 'test',
					'value' => 'test value',
					'data'  => ['some' => 'data'],
					'attrs' => []
				]
			],
			[
				'test: test value',
				[],
				[
					'type'  => 'test',
					'value' => 'test value',
					'attrs' => []
				]
			],
			[
				'test:',
				[],
				[
					'type'  => 'test',
					'value' => '',
					'attrs' => []
				]
			],
			[
				'test: ',
				[],
				[
					'type'  => 'test',
					'value' => '',
					'attrs' => []
				]
			],
			[
				'test: test value a: attrA b: attrB',
				[],
				[
					'type'  => 'test',
					'value' => 'test value',
					'attrs' => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test:test value a:attrA b:attrB',
				[],
				[
					'type'  => 'test',
					'value' => 'test value',
					'attrs' => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA b:',
				[],
				[
					'type'  => 'test',
					'value' => 'test value',
					'attrs' => [
						'a' => 'attrA',
						'b' => ''
					]
				]
			],
			[
				'test: test value a: attrA b: ',
				[],
				[
					'type'  => 'test',
					'value' => 'test value',
					'attrs' => [
						'a' => 'attrA',
						'b' => ''
					]
				]
			],
			[
				'test: test value a: attrA b: attrB ',
				[],
				[
					'type'  => 'test',
					'value' => 'test value',
					'attrs' => [
						'a' => 'attrA',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA c: attrC b: attrB',
				[],
				[
					'type'  => 'test',
					'value' => 'test value',
					'attrs' => [
						'a' => 'attrA c: attrC',
						'b' => 'attrB'
					]
				]
			],
			[
				'test: test value a: attrA b: attrB c: attrC',
				[],
				[
					'type'  => 'test',
					'value' => 'test value',
					'attrs' => [
						'a' => 'attrA',
						'b' => 'attrB c: attrC'
					]
				]
			],
			[
				'legacy: file://abc a: attrA b: attrB c: attrC',
				[],
				[
					'type'  => 'legacy',
					'value' => 'file://abc',
					'attrs' => [
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
		array $expected
	): void {
		$tag = KirbyTag::parse($string, $data);

		foreach ($expected as $key => $value) {
			$this->assertSame($value, $tag->$key);
		}
	}

	public function testRender(): void
	{
		$tag = KirbyTag::factory('test', 'test value', [
			'a' => 'attrA',
			'b' => 'attrB'
		]);
		$this->assertSame('test: test value-attrA-attrB', $tag->render());

		$tag = KirbyTag::factory('test', '', [
			'a' => 'attrA'
		]);
		$this->assertSame('test: -attrA-', $tag->render());
	}

	public function testType(): void
	{
		$tag = KirbyTag::factory('test', 'test value');
		$this->assertSame('test', $tag->type());
	}
}
