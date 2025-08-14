<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Cms\Files;
use Kirby\Cms\Layouts;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\Users;
use Kirby\Data\Data;
use Kirby\Data\Json;
use Kirby\Data\Yaml;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Image\QrCode;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Field::class)]
class FieldTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Content.Field';

	public function setUp(): void
	{
		parent::setUp();

		new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::FIXTURES
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		parent::tearDown();
		Dir::remove(static::TMP);
		App::destroy();
	}

	protected function field($value = '', $parent = null): Field
	{
		return new Field(
			key:   'test',
			value:  $value,
			parent: $parent
		);
	}

	public function test__call(): void
	{
		Field::$methods['foo'] = fn (Field $field, string $bar) => $bar;

		$field = $this->field('test');
		$this->assertSame('bar', $field->foo('bar'));
		$this->assertSame('bar', $field->FOO('bar'));
	}

	public function test__callNonExistingMethod(): void
	{
		$field  = $this->field('value');
		$result = $field->methodDoesNotExist();

		$this->assertSame($field, $result);
	}

	public function test__debugInfo(): void
	{
		$field = $this->field('Title');
		$this->assertSame(['test' => 'Title'], $field->__debugInfo());
	}

	public function testFieldMethodCaseInsensitivity(): void
	{
		$field = $this->field('test');
		$this->assertSame('TEST', $field->upper()->value());
		$this->assertSame('TEST', $field->UPPER()->value());
	}

	public function testFieldMethodChained(): void
	{
		$field = $this->field('test')->upper()->short(3);
		$this->assertSame('TES…', $field->value());
	}

	public function testFieldMethodImmutable(): void
	{
		Field::$methods = [
			'test' => function ($field) {
				$field->value = 'Test';
				return $field;
			}
		];

		$original = $this->field('Title');
		$modified = $original->test();

		$this->assertSame('Title', $original->value());
		$this->assertSame('Test', $modified->value());
	}

	public function testCallback(): void
	{
		$field  = $this->field('Hello world');
		$result = $field->callback(function ($field) {
			$field->value = 'foo';
			return $field;
		});
		$this->assertSame('foo', $result->toString());
	}

	public function testEscape(): void
	{
		$expected = '&lt;script&gt;alert(&quot;hello&quot;)&lt;/script&gt;';

		$field = $this->field('<script>alert("hello")</script>');
		$this->assertSame($expected, $field->escape()->value());

		// alias
		$field = $this->field('<script>alert("hello")</script>');
		$this->assertSame($expected, $field->esc()->value());
	}

	public function testExcerpt(): void
	{
		// html
		$string   = 'This is a long text<br>with some html';
		$expected = 'This is a long text with …';

		$this->assertSame($expected, $this->field($string)->excerpt(27)->value());

		// markdown
		$string   = 'This is a long text **with some** html';
		$expected = 'This is a long text with …';

		$this->assertSame($expected, $this->field($string)->excerpt(27)->value());
	}

	public function testExists(): void
	{
		$parent = new Page([
			'slug' => 'test',
			'content' => [
				'a' => 'Value A'
			]
		]);

		$this->assertTrue($parent->a()->exists());
		$this->assertFalse($parent->b()->exists());
	}

	public function testHtml(): void
	{
		$expected = '&ouml;';

		$field = $this->field('ö');
		$this->assertSame($expected, $field->html()->value());

		// alias
		$field = $this->field('ö');
		$this->assertSame($expected, $field->h()->value());
	}

	public function testInline(): void
	{
		$html = '<div><h1>Headline</h1> <p>Subtitle with <a href="#">link</a>.</p></div>';
		$expected = 'Headline Subtitle with <a href="#">link</a>.';

		$this->assertSame($expected, $this->field($html)->inline()->value());
		$this->assertSame('', $this->field(null)->inline()->value());
	}

	public static function emptyDataProvider(): array
	{
		return [
			['test', false],
			['0', false],
			[0, false],
			[true, false],
			['true', false],
			[false, false],
			['false', false],
			[null, true],
			['', true],
			['   ', true],
			['[]', true],
			[[], true],
			['[1]', false],
			['["a"]', false],
		];
	}

	#[DataProvider('emptyDataProvider')]
	public function testIsEmpty($input, $expected): void
	{
		$field = $this->field($input);
		$this->assertSame($expected, $field->isEmpty());
	}

	public function testIsFalse(): void
	{
		$this->assertTrue($this->field('false')->isFalse());
		$this->assertTrue($this->field(false)->isFalse());
	}

	#[DataProvider('emptyDataProvider')]
	public function testIsNotEmpty($input, $expected): void
	{
		$field = $this->field($input);
		$this->assertSame(!$expected, $field->isNotEmpty());
	}

	public function testIsTrue(): void
	{
		$this->assertTrue($this->field('true')->isTrue());
		$this->assertTrue($this->field(true)->isTrue());
	}

	public function testIsValid(): void
	{
		$this->assertTrue($this->field('mail@example.com')->isValid('email'));
		$this->assertTrue($this->field('https://example.com')->isValid('url'));

		// alias
		$this->assertTrue($this->field('mail@example.com')->v('email'));
		$this->assertTrue($this->field('https://example.com')->v('url'));
	}

	public function testKey(): void
	{
		$field = $this->field('Foo');
		$this->assertSame('test', $field->key());
	}

	public function testKirbytags(): void
	{
		$kirbytext = '(link: # text: Test)';
		$expected  = '<a href="#">Test</a>';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytags()->value());
	}

	public function testKirbytext(): void
	{
		$kirbytext = '(link: # text: Test)';
		$expected  = '<p><a href="#">Test</a></p>';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytext()->value());

		// alias
		$this->assertSame($expected, $this->field($kirbytext)->kt()->value());
	}

	public function testKirbytextWithSafeMode(): void
	{
		$kirbytext = '<h1>Test</h1>';
		$expected  = '<p>&lt;h1&gt;Test&lt;/h1&gt;</p>';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytext(['markdown' => ['safe' => true]])->value());
	}

	public function testKirbytextInline(): void
	{
		$kirbytext = '(link: # text: Test)';
		$expected  = '<a href="#">Test</a>';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytextinline()->value());

		// alias
		$this->assertSame($expected, $this->field($kirbytext)->kti()->value());
	}

	public function testKirbytextInlineWithSafeMode(): void
	{
		$kirbytext = '<b>Test</b>';
		$expected  = '&lt;b&gt;Test&lt;/b&gt;';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytextInline(['markdown' => ['safe' => true]])->value());
	}

	public function testLength(): void
	{
		$this->assertSame(3, $this->field('abc')->length());
	}

	public function testLower(): void
	{
		$this->assertSame('abc', $this->field('ABC')->lower()->value());
	}

	public function testMarkdown(): void
	{
		$markdown = '**Test**';
		$expected = '<p><strong>Test</strong></p>';

		$this->assertSame($expected, $this->field($markdown)->markdown()->value());

		// alias
		$this->assertSame($expected, $this->field($markdown)->md()->value());
	}

	public function testMarkdownWithSafeMode(): void
	{
		$markdown = '<h1>Test</h1>';
		$expected = '<p>&lt;h1&gt;Test&lt;/h1&gt;</p>';

		$this->assertSame($expected, $this->field($markdown)->markdown(['safe' => true])->value());
	}

	public function testModel(): void
	{
		$model = new Page(['slug' => 'test']);
		$field = $this->field(parent: $model);

		$this->assertSame($model, $field->model());
	}

	public function testNl2br(): void
	{
		$input = 'Multiline' . PHP_EOL . 'test' . PHP_EOL . 'string';
		$expected = 'Multiline<br>' . PHP_EOL . 'test<br>' . PHP_EOL . 'string';

		$this->assertSame($expected, $this->field($input)->nl2br()->value());
		$this->assertSame('', $this->field(null)->nl2br()->value());
	}

	public function testOr(): void
	{
		// with value, fallback is ignored
		$fallback = $this->field('fallback value');
		$field    = $this->field('field value');

		$this->assertSame($field, $field->or($fallback));

		// with field fallback
		$fallback = $this->field('fallback value');
		$field    = $this->field('');

		$this->assertSame($fallback, $fallback->or($field));
		$this->assertSame($fallback, $field->or($fallback));

		// with string fallback
		$fallback = 'fallback value';
		$field    = $this->field('');
		$result   = $field->or($fallback);

		$this->assertNotSame($field, $result);
		$this->assertSame($fallback, $result->value());
	}

	public function testParent(): void
	{
		$parent = new Page(['slug' => 'test']);
		$field  = $this->field(parent: $parent);

		$this->assertSame($parent, $field->parent());
	}

	public function testPermalinksToUrls(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'uuid' => 'my-page'
						],
						'files' => [
							[
								'filename' => 'test.jpg',
								'content' => [
									'uuid'  => 'my-file',
								]
							]
						]
					],
				]
			]
		]);

		$field  = $this->field('<p>This is a <a href="/@/page/my-page">test</a><img src="/@/file/my-file"></p>. This should not be <a href="https://getkirby.com">affected</a>.');
		$result = $field->permalinksToUrls();
		$hash   = $app->file('a/test.jpg')->mediaHash();

		$this->assertSame('<p>This is a <a href="/a">test</a><img src="/media/pages/a/' . $hash . '/test.jpg"></p>. This should not be <a href="https://getkirby.com">affected</a>.', (string)$result);
	}

	public function testPermalinksToUrlsWithMissingUUID(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
		]);

		$field  = $this->field('<p>This is a <a href="/@/page/my-page">test</a></p>.');
		$result = $field->permalinksToUrls();

		$this->assertSame('<p>This is a <a href="/@/page/my-page">test</a></p>.', (string)$result);
	}

	public function testQuery(): void
	{
		// with page
		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Hello world',
				'text'  => 'page.title'
			]
		]);

		$this->assertSame('Hello world', $page->text()->query()->value());
	}

	public function testQueryWithoutParent(): void
	{
		new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'content' => [
					'title' => 'Kirby',
				]
			]
		]);

		$field = $this->field('site.title');
		$this->assertSame('Kirby', $field->query()->value());
	}

	public function testReplace(): void
	{
		// simple replacement
		$this->assertSame('Hello world', $this->field('Hello {{ message }}')->replace(['message' => 'world'])->value());

		// nested replacement
		$this->assertSame('Hello world', $this->field('Hello {{ message.text }}')->replace([
			'message' => [
				'text' => 'world'
			]
		])->value());

		// missing or empty field
		$this->assertSame('', $this->field(null)->replace(['message' => 'world'])->value());
		$this->assertSame('', $this->field('')->replace(['message' => 'world'])->value());

		// with page
		$page = new Page([
			'slug'    => 'test',
			'content' => [
				'title' => 'Hello world',
				'text'  => 'Title: {{ page.title }}'
			]
		]);

		$this->assertSame('Title: Hello world', $page->text()->replace()->value());
		$this->assertSame('', $page->doesNotExist()->replace()->value());

		// with fallback
		$this->assertSame(
			'Hello ',
			$this->field('Hello {{ invalid }}')->replace(['message' => 'world'])->value()
		);
		$this->assertSame(
			'Hello fallback',
			$this->field('Hello {{ invalid }}')->replace(['message' => 'world'], 'fallback')->value()
		);
		$this->assertSame(
			'Hello {{ invalid }}',
			$this->field('Hello {{ invalid }}')->replace(['message' => 'world'], null)->value()
		);
	}

	public function testShort(): void
	{
		$this->assertSame('abc…', $this->field('abcd')->short(3)->value());
	}

	public function testSlug(): void
	{
		$text     = 'Ä--Ö--Ü';
		$expected = 'a-o-u';

		$this->assertSame($expected, $this->field($text)->slug()->value());
	}

	public function testSmartypants(): void
	{
		$text     = '"Test"';
		$expected = '&#8220;Test&#8221;';

		$this->assertSame($expected, $this->field($text)->smartypants()->value());

		// alias
		$this->assertSame($expected, $this->field($text)->sp()->value());
	}

	public function testSmartypantsWithKirbytext(): void
	{
		new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'smartypants' => true
			]
		]);

		$text     = '"Test"';
		$expected = '&#8220;Test&#8221;';

		$this->assertSame($expected, $this->field($text)->kti()->value());
	}

	public function testSplit(): void
	{
		$text = 'a, b, c';
		$expected = ['a', 'b', 'c'];

		$this->assertSame($expected, $this->field($text)->split());
	}

	public function testToArray(): void
	{
		$field = $this->field('Title');
		$this->assertSame(['test' => 'Title'], $field->toArray());
	}

	public function testToBlocks(): void
	{
		$data = [
			[
				'type' => 'code',
				'content' => [
					'code' => '<?php echo "Hello World!"; ?>',
					'language' => 'php',
				]
			],
			[
				'type' => 'gallery',
				'content' => [
					'images' => [
						'a.png',
						'b.png'
					],
				]
			],
			[
				'type'    => 'image',
				'content' => [
					'alt'      => 'The Kirby logo as favicon',
					'caption'  => 'This favicon is really amazing!',
					'location' => 'web',
					'src'      => 'https://getkirby.com/favicon.png',
					'link'     => 'https://getkirby.com',
				]
			],
			[
				'type'    => 'image',
				'content' => [
					'alt'   => 'White ink on a white canvas',
					'image' => 'a.png',
				]
			],
			[
				'type'    => 'heading',
				'content' => [
					'text' => 'A nice heading',
				]
			],
			[
				'type'    => 'list',
				'content' => [
					'text' => '<ul><li>list item 1<\/li><li>list item 2<\/li><\/ul>',
				]
			],
			[
				'type'    => 'markdown',
				'content' => [
					'text' => '# Heading 1',
				]
			],
			[
				'type'    => 'quote',
				'content' => [
					'text'     => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus in ultricies lorem. Fusce vulputate placerat urna sed pellentesque.',
					'citation' => 'John Doe',
				]
			],
			[
				'type'    => 'text',
				'content' => [
					'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus in ultricies lorem. Fusce vulputate placerat urna sed pellentesque.'
				]
			],
			[
				'type'    => 'video',
				'content' => [
					'caption' => 'How to install Kirby in 5 minutes',
					'url'     => 'https://www.youtube.com/watch?v=EDVYjxWMecc',
				]
			]
		];

		$json   = Json::encode($data);
		$page   = kirby()->page('files');
		$field  = $this->field($json, $page);
		$blocks = $field->toBlocks();

		$this->assertInstanceOf(Blocks::class, $blocks);
		$this->assertIsPage($blocks->parent());
		$this->assertCount(count($data), $blocks);
		$this->assertCount(count($data), $blocks->data());

		foreach ($data as $index => $row) {
			$block = $blocks->nth($index);

			$this->assertSame($page, $block->parent());
			$this->assertEquals($field, $block->field()); // cannot use strict assertion (cloned object)
			$this->assertSame($row['type'], $block->type());
			$this->assertSame($row['content'], $block->content()->data());
			$this->assertNotEmpty($block->toHtml());
		}
	}

	public function testToBlocksWithInvalidData(): void
	{
		$data = [
			[
				'content' => [
					'text' => 'foo',
				]
			]
		];

		$json   = Json::encode($data);
		$field  = $this->field($json, kirby()->page('files'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid blocks data for "test" field on parent "files"');

		$field->toBlocks();
	}

	public function testToBool(): void
	{
		$this->assertTrue($this->field('1')->toBool());
		$this->assertTrue($this->field('true')->toBool());
		$this->assertFalse($this->field('0')->toBool());
		$this->assertFalse($this->field('false')->toBool());

		// alias
		$this->assertFalse($this->field('false')->bool());
	}

	public function testToDataSplit(): void
	{
		$this->assertSame(['a', 'b'], $this->field('a, b')->toData());
	}

	public function testToDataSplitWithDifferentSeparator(): void
	{
		$this->assertSame(['a', 'b'], $this->field('a; b')->toData(';'));
	}

	public function testToDataYaml(): void
	{
		$data = ['a', 'b'];

		$this->assertSame(['a', 'b'], $this->field(Yaml::encode($data))->toData('yaml'));
	}

	public function testToDataJson(): void
	{
		$data = ['a', 'b'];

		$this->assertSame(['a', 'b'], $this->field(json_encode($data))->toData('json'));
	}

	public function testToDate(): void
	{
		$field = $this->field('2012-12-12');
		$ts    = strtotime('2012-12-12');
		$date  = '12.12.2012';

		$this->assertSame($ts, $field->toDate());
		$this->assertSame($date, $field->toDate('d.m.Y'));
	}

	public function testToDateWithDateHandler(): void
	{
		new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'date.handler' => 'strftime'
			]
		]);

		$field = $this->field('2012-12-12');
		$ts    = strtotime('2012-12-12');
		$date  = '12.12.2012';

		$this->assertSame($ts, $field->toDate());
		$this->assertSame($date, $field->toDate('%d.%m.%Y'));
	}

	public function testToDateWithFallback(): void
	{
		$field = $this->field(null);
		$date  = '12.12.2012';

		$this->assertSame($date, $field->toDate('d.m.Y', '2012-12-12'));
		$this->assertSame(date('d.m.Y'), $field->toDate('d.m.Y', 'today'));
	}

	public function testToDateWithEmptyValueAndNoFallback(): void
	{
		$field = $this->field(null);
		$this->assertNull($field->toDate('d.m.Y'));
	}

	public function testToEntries(): void
	{
		$value   = [
			'Text',
			'Some text',
			'Another text',
		];
		$page    = new Page(['slug' => 'test']);
		$field   = $this->field(Data::encode($value, 'yaml'), $page);
		$entries = $field->toEntries();

		$this->assertInstanceOf(Collection::class, $entries);
		$this->assertSame($page, $entries->parent());
		$this->assertCount(3, $entries);
		$this->assertInstanceOf(Field::class, $entries->first());
		$this->assertSame('Text', $entries->first()->value());
		$this->assertSame('TEXT', $entries->first()->upper()->value());
		$this->assertSame('Some text', $entries->nth(1)->value());
		$this->assertSame('Another text', $entries->nth(2)->value());
	}

	public function testToEntriesDateMethod(): void
	{
		$value   = ['2012-12-12'];
		$page    = new Page(['slug' => 'test']);
		$field   = $this->field(Data::encode($value, 'yaml'), $page);
		$entries = $field->toEntries();

		$this->assertInstanceOf(Collection::class, $entries);
		$this->assertSame($page, $entries->parent());
		$this->assertCount(1, $entries);
		$this->assertInstanceOf(Field::class, $entries->first());
		$this->assertSame('2012-12-12', $entries->first()->value());
		$this->assertSame('12.12.2012', $entries->first()->toDate('d.m.Y'));
	}

	public function testToEntriesEmptyValue(): void
	{
		$field   = $this->field();
		$entries = $field->toEntries();

		$this->assertInstanceOf(Collection::class, $entries);
		$this->assertCount(0, $entries);
	}

	public function testToFile(): void
	{
		new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$page = new Page([
			'content' => [
				'cover'   => 'cover.jpg',
				'coverid' => 'file://file-cover-uuid'
			],
			'files' => [
				[
					'filename' => 'cover.jpg',
					'content'  => ['uuid' => 'file-cover-uuid']
				]
			],
			'slug' => 'test'
		]);

		$this->assertSame('cover.jpg', $page->cover()->toFile()->filename());
		$this->assertSame('cover.jpg', $page->coverid()->toFile()->filename());
	}

	public function testToFiles(): void
	{
		$page = new Page([
			'content' => [
				'gallery' => Yaml::encode(['a.jpg', 'b.jpg'])
			],
			'files' => [
				['filename' => 'a.jpg'],
				['filename' => 'b.jpg']
			],
			'slug' => 'test'
		]);

		$this->assertSame($page->files()->pluck('filename'), $page->gallery()->toFiles()->pluck('filename'));
	}

	public function testToFilesFromDifferentPage(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'gallery' => Yaml::encode(['b/b.jpg', 'a/a.jpg'])
						],
						'files' => [
							['filename' => 'a.jpg']
						]
					],
					[
						'slug' => 'b',
						'files' => [
							['filename' => 'b.jpg']
						]
					]
				]
			]
		]);

		$page = $app->page('a');

		$this->assertSame(['b.jpg', 'a.jpg'], $page->gallery()->toFiles()->pluck('filename'));
	}

	public function testToFilesWithoutResults(): void
	{
		$page = new Page([
			'content' => [
				'gallery' => Yaml::encode(['a.jpg', 'b.jpg'])
			],
			'files' => [
			],
			'slug' => 'test'
		]);

		$this->assertInstanceOf(Files::class, $page->gallery()->toFiles());
	}

	public function testToFloat(): void
	{
		$field    = $this->field('1.2');
		$expected = 1.2;

		$this->assertSame($expected, $field->toFloat());

		// alias
		$this->assertSame($expected, $field->float());
	}

	public function testToInt(): void
	{
		$this->assertSame(1, $this->field('1')->toInt());
		$this->assertIsInt($this->field('1')->toInt());

		// alias
		$this->assertSame(1, $this->field('1')->int());
	}

	public function testToLayouts(): void
	{
		$data = [
			[
				'type'    => 'heading',
				'content' => ['text' => 'Heading'],
			],
			[
				'type'    => 'text',
				'content' => ['text' => 'Text'],
			]
		];

		$page    = kirby()->page('files');
		$field   = $this->field(json_encode($data), $page);
		$layouts = $field->toLayouts();
		$blocks  = $layouts->toBlocks();

		$this->assertInstanceOf(Layouts::class, $layouts);
		$this->assertSame($page, $layouts->parent());
		$this->assertCount(1, $layouts->data());

		$layout = $layouts->first();
		$this->assertSame($page, $layout->parent());
		$this->assertEquals($field, $layout->field()); // cannot use strict assertion (cloned object)

		$array = $layout->toArray();
		$this->assertArrayHasKey('attrs', $array);
		$this->assertArrayHasKey('columns', $array);
		$this->assertArrayHasKey('id', $array);

		$block = $blocks->first();
		$this->assertSame($page, $block->parent());
		$this->assertEquals($field, $block->field());
	}

	public function testToLink(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$expected = '<a href="/test">Test</a>';

		$this->assertSame($expected, $page->title()->toLink());

		// alias
		$this->assertSame($expected, $page->title()->link());
	}

	public function testToLinkWithHref(): void
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$expected = '<a class="test" href="https://getkirby.com">Test</a>';

		$this->assertSame($expected, $page->title()->toLink('https://getkirby.com', ['class' => 'test']));
	}

	public function testToLinkWithActivePage(): void
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'test',
					'content' => [
						'title' => 'Test'
					]
				]
			]
		]);

		$page     = $site->visit('test');
		$expected = '<a aria-current="page" href="/test">Test</a>';

		$this->assertSame($expected, $page->title()->toLink());
	}

	public function testToObject(): void
	{
		$data = [
			'heading' => 'Heading',
			'text'    => 'Text'
		];

		$field  = $this->field(Yaml::encode($data));
		$object = $field->toObject();

		$this->assertInstanceOf(Content::class, $object);

		$this->assertSame('Heading', $object->heading()->value());
	}

	public function testToPage(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					['slug' => 'a'],
					['slug' => 'b'],
					['slug' => 'c', 'content' => ['uuid' => 'uuid-c']]
				]
			]
		]);

		$a = $app->page('a');
		$b = $app->page('b');
		$c = $app->page('c');

		$this->assertSame($a, $this->field('a')->toPage());
		$this->assertSame($b, $this->field('b')->toPage());
		$this->assertSame($c, $this->field('page://uuid-c')->toPage());

		$this->assertSame($a, $this->field(Yaml::encode(['a']))->toPage());
		$this->assertSame($b, $this->field(Yaml::encode(['b', 'a']))->toPage());
		$this->assertSame($c, $this->field(Yaml::encode(['page://uuid-c', 'b', 'a']))->toPage());
	}

	public function testToPages(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					['slug' => 'a'],
					['slug' => 'b'],
				]
			]
		]);

		$a = $app->page('a');
		$b = $app->page('b');

		// single page
		$pages = new Pages([$a], $app->site());

		$content = Yaml::encode([
			'a',
		]);

		$result = $this->field($content)->toPages();
		$this->assertInstanceOf(Pages::class, $result);
		$this->assertSame(['a' => $a], $result->data());

		// multiple pages
		$pages = new Pages([$a, $b], $app->site());

		$content = Yaml::encode([
			'a',
			'b'
		]);

		$result = $this->field($content)->toPages();
		$this->assertInstanceOf(Pages::class, $result);
		$this->assertSame($pages->data(), $result->data());

		// no results
		$content = Yaml::encode([
			'c',
			'd'
		]);

		$result = $this->field($content)->toPages();
		$this->assertInstanceOf(Pages::class, $result);
		$this->assertSame([], $result->data());
	}

	public function testToQrCode(): void
	{
		$field = $this->field($url = 'https://getkirby.com');
		$qr    = $field->toQrCode();

		$this->assertInstanceOf(QrCode::class, $qr);
		$this->assertSame($url, $qr->data);

		$this->assertNull($this->field()->toQrCode());
	}

	public function testToString(): void
	{
		$field = $this->field('Title');

		$this->assertSame('Title', $field->toString());
		$this->assertSame('Title', $field->__toString());
		$this->assertSame('Title', (string)$field);
	}

	public function testToStructure(): void
	{
		$data = [
			['title' => 'a', 'field' => 'c'],
			['title' => 'b', 'field' => 'd']
		];

		$yaml = Yaml::encode($data);

		$field     = $this->field($yaml);
		$structure = $field->toStructure();

		$this->assertCount(2, $structure);
		$this->assertEquals($field, $structure->field()); // Field object gets cloned by the `Field` class
		$this->assertEquals($field, $structure->first()->field()); // Field object gets cloned by the `Field` class
		$this->assertSame('a', $structure->first()->title()->value());
		$this->assertSame('a', $structure->first()->content()->title()->value());
		$this->assertSame('c', $structure->first()->content()->field()->value());
		$this->assertSame('b', $structure->last()->title()->value());
		$this->assertSame('b', $structure->last()->content()->title()->value());
		$this->assertSame('d', $structure->last()->content()->field()->value());
	}

	public function testToStructureWithInvalidData(): void
	{
		$data = [
			['title' => 'a'],
			['title' => 'b'],
			'title'
		];

		$yaml  = Yaml::encode($data);
		$field = $this->field($yaml, kirby()->page('files'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid structure data for "test" field on parent "files"');

		$field->toStructure();
	}

	public function testToTimestamp(): void
	{
		$field = $this->field('2012-12-12');
		$ts    = strtotime('2012-12-12');

		$this->assertSame($ts, $field->toTimestamp());
		$this->assertFalse($this->field(null)->toTimestamp());
	}

	public function testToUrlDefault(): void
	{
		$field    = $this->field('super/cool');
		$expected = '/super/cool';

		$this->assertSame($expected, $field->toUrl());
	}

	public function testToUrlCustom(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$field    = $this->field('super/cool');
		$expected = 'https://getkirby.com/super/cool';

		$this->assertSame($expected, $field->toUrl());
	}

	public function testToUrlEmpty(): void
	{
		$field = $this->field();
		$this->assertNull($field->toUrl());
	}

	public function testToUrlUuid(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'title' => 'Test Title',
							'uuid'  => 'my-page-uuid'
						],
						'files' => [
							[
								'filename' => 'test.jpg',
								'content' => [
									'uuid'  => 'my-file-uuid',
								]
							]
						]
					]
				]
			]
		]);

		$page = $app->page('page://my-page-uuid');
		$field = $this->field('page://my-page-uuid');
		$this->assertSame('/test', $field->toUrl());
		$this->assertSame($page->url(), $field->toUrl());

		$file = $app->file('file://my-file-uuid');
		$field = $this->field('file://my-file-uuid');
		$this->assertSame('/media/pages/test/' . $file->mediaHash() . '/test.jpg', $field->toUrl());
		$this->assertSame($file->url(), $field->toUrl());
	}

	public function testToUrlInvalidUuid(): void
	{
		$field = $this->field('page://invalid');
		$this->assertNull($field->toUrl());

		$field = $this->field('file://invalid');
		$this->assertNull($field->toUrl());
	}

	public function testToUser(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				['email' => 'a@company.com'],
				['email' => 'b@company.com'],
				['email' => 'c@company.com', 'id' => 'my-user']
			]
		]);

		$a = $app->user('a@company.com');
		$b = $app->user('b@company.com');
		$c = $app->user('c@company.com');

		$this->assertSame($a, $this->field('a@company.com')->toUser());
		$this->assertSame($b, $this->field('b@company.com')->toUser());
		$this->assertSame($c, $this->field('user://my-user')->toUser());

		$this->assertSame($a, $this->field(Yaml::encode(['a@company.com']))->toUser());
		$this->assertSame($b, $this->field(Yaml::encode(['b@company.com', 'a@company.com']))->toUser());
	}

	public function testToUsers(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				['email' => 'a@company.com'],
				['email' => 'b@company.com']
			]
		]);

		// two results
		$content = Yaml::encode([
			'a@company.com',
			'b@company.com'
		]);

		$this->assertSame(['a@company.com', 'b@company.com'], $this->field($content)->toUsers()->pluck('email'));

		// no results
		$content = Yaml::encode([
			'c@company.com',
			'd@company.com'
		]);

		$this->assertInstanceOf(Users::class, $this->field($content)->toUsers());
	}

	public function testUpper(): void
	{
		$this->assertSame('ABC', $this->field('abc')->upper()->value());
	}

	public function testValue(): void
	{
		$field = $this->field('Title');
		$this->assertSame('Title', $field->value());
	}

	public function testValueSetter(): void
	{
		$field = $this->field('Title');
		$this->assertSame('Title', $field->value());
		$field = $field->value('Modified');
		$this->assertSame('Modified', $field->value());
	}

	public function testValueCallbackSetter(): void
	{
		$field = $this->field('Title');
		$this->assertSame('Title', $field->value());
		$field = $field->value(fn ($value) => 'Modified');
		$this->assertSame('Modified', $field->value());
	}

	public function testWidont(): void
	{
		$this->assertSame('Test&nbsp;Headline', $this->field('Test Headline')->widont()->value());
		$this->assertSame('Test Headline&nbsp;With&#8209;Dash', $this->field('Test Headline With-Dash')->widont()->value());
	}

	public function testWords(): void
	{
		$text = 'this is an example text';
		$this->assertSame(5, $this->field($text)->words());
		$this->assertSame(0, $this->field(null)->words());
	}

	public function testXml(): void
	{
		$expected = '&#246;&#228;&#252;';

		$field = $this->field('öäü');
		$this->assertSame($expected, $field->xml()->value());

		// alias
		$field = $this->field('öäü');
		$this->assertSame($expected, $field->x()->value());
	}

	public function testYaml(): void
	{
		$data = [
			'a',
			'b',
			'c'
		];

		$yaml = Yaml::encode($data);
		$this->assertSame($data, $this->field($yaml)->yaml());
	}
}
