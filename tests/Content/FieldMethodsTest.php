<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Blocks;
use Kirby\Cms\Files;
use Kirby\Cms\Layouts;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\Users;
use Kirby\Data\Json;
use Kirby\Data\Yaml;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

class FieldMethodsTest extends TestCase
{
	protected $tmp;

	public function setUp(): void
	{
		parent::setUp();

		new App([
			'roots' => [
				'index'   => $this->tmp = __DIR__ . '/tmp',
				'content' => __DIR__ . '/fixtures'
			]
		]);

		Dir::make($this->tmp);
	}

	public function tearDown(): void
	{
		parent::tearDown();
		Dir::remove($this->tmp);
	}

	public function testFieldMethodCaseInsensitivity()
	{
		$field = $this->field('test');
		$this->assertSame('TEST', $field->upper()->value());
		$this->assertSame('TEST', $field->UPPER()->value());
	}

	public function testFieldMethodAliasCaseInsensitivity()
	{
		$field = $this->field('1');
		$this->assertSame(1, $field->toInt());
		$this->assertSame(1, $field->int());
	}

	public function testFieldMethodCombination()
	{
		$field = $this->field('test')->upper()->short(3);
		$this->assertSame('TES…', $field->value());
	}

	public function testIsFalse()
	{
		$this->assertTrue($this->field('false')->isFalse());
		$this->assertTrue($this->field(false)->isFalse());
	}

	public function testIsTrue()
	{
		$this->assertTrue($this->field('true')->isTrue());
		$this->assertTrue($this->field(true)->isTrue());
	}

	public function testIsValid()
	{
		$this->assertTrue($this->field('mail@example.com')->isValid('email'));
		$this->assertTrue($this->field('https://example.com')->isValid('url'));
	}

	public function testToDataSplit()
	{
		$this->assertSame(['a', 'b'], $this->field('a, b')->toData());
	}

	public function testToDataSplitWithDifferentSeparator()
	{
		$this->assertSame(['a', 'b'], $this->field('a; b')->toData(';'));
	}

	public function testToDataYaml()
	{
		$data = ['a', 'b'];

		$this->assertSame(['a', 'b'], $this->field(Yaml::encode($data))->toData('yaml'));
	}

	public function testToDataJson()
	{
		$data = ['a', 'b'];

		$this->assertSame(['a', 'b'], $this->field(json_encode($data))->toData('json'));
	}

	public function testToBool()
	{
		$this->assertTrue($this->field('1')->toBool());
		$this->assertTrue($this->field('true')->toBool());
		$this->assertFalse($this->field('0')->toBool());
		$this->assertFalse($this->field('false')->toBool());
	}

	public function testToDate()
	{
		$field = $this->field('2012-12-12');
		$ts    = strtotime('2012-12-12');
		$date  = '12.12.2012';

		$this->assertSame($ts, $field->toDate());
		$this->assertSame($date, $field->toDate('d.m.Y'));
	}

	public function testToDateWithDateHandler()
	{
		new App([
			'roots' => [
				'index' => $this->tmp
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

	public function testToDateWithFallback()
	{
		$field = $this->field(null);
		$date  = '12.12.2012';

		$this->assertSame($date, $field->toDate('d.m.Y', '2012-12-12'));
		$this->assertSame(date('d.m.Y'), $field->toDate('d.m.Y', 'today'));
	}

	public function testToDateWithEmptyValueAndNoFallback()
	{
		$field = $this->field(null);
		$this->assertNull($field->toDate('d.m.Y'));
	}

	public function testToFile()
	{
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
		Dir::remove(__DIR__ . '/fixtures/test');
	}

	public function testToFiles()
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

	public function testToFilesFromDifferentPage()
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
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

	public function testToFilesWithoutResults()
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

	public function testToFloat()
	{
		$field    = $this->field('1.2');
		$expected = 1.2;

		$this->assertSame($expected, $field->toFloat());
	}

	public function testToInt()
	{
		$this->assertSame(1, $this->field('1')->toInt());
		$this->assertTrue(is_int($this->field('1')->toInt()));
	}

	public function testToLink()
	{
		$page = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$expected = '<a href="/test">Test</a>';

		$this->assertSame($expected, $page->title()->toLink());
	}

	public function testToLinkWithHref()
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

	public function testToLinkWithActivePage()
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

	public function testToPage()
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
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

	public function testToPages()
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
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

	public function testToStructure()
	{
		$data = [
			['title' => 'a'],
			['title' => 'b']
		];

		$yaml = Yaml::encode($data);

		$field     = $this->field($yaml);
		$structure = $field->toStructure();

		$this->assertCount(2, $structure);
		$this->assertSame('a', $structure->first()->title()->value());
		$this->assertSame('b', $structure->last()->title()->value());
	}

	public function testToStructureWithInvalidData()
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

	public function testToTimestamp()
	{
		$field = $this->field('2012-12-12');
		$ts    = strtotime('2012-12-12');

		$this->assertSame($ts, $field->toTimestamp());
		$this->assertFalse($this->field(null)->toTimestamp());
	}

	public function testToDefaultUrl()
	{
		$field    = $this->field('super/cool');
		$expected = '/super/cool';

		$this->assertSame($expected, $field->toUrl());
	}

	public function testToCustomUrl()
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$field    = $this->field('super/cool');
		$expected = 'https://getkirby.com/super/cool';

		$this->assertSame($expected, $field->toUrl());
	}

	public function testToUrlEmpty()
	{
		$field = $this->field();
		$this->assertNull($field->toUrl());
	}

	public function testToUuidUrl()
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
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

	public function testToInvalidUuidUrl()
	{
		$field = $this->field('page://invalid');
		$this->assertNull($field->toUrl());

		$field = $this->field('file://invalid');
		$this->assertNull($field->toUrl());
	}

	public function testToUser()
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
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

	public function testToUsers()
	{
		$app = new App([
			'roots' => [
				'index' => $this->tmp
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

	public function testLength()
	{
		$this->assertSame(3, $this->field('abc')->length());
	}

	public function testCallback()
	{
		$field  = $this->field('Hello world');
		$result = $field->callback(function ($field) {
			$field->value = 'foo';
			return $field;
		});
		$this->assertSame('foo', $result->toString());
	}

	public function testEscape()
	{
		$this->assertSame('&lt;script&gt;alert(&quot;hello&quot;)&lt;/script&gt;', $this->field('<script>alert("hello")</script>')->escape()->value());
	}

	public function testExcerpt()
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

	public function testHtml()
	{
		$this->assertSame('&ouml;', $this->field('ö')->html()->value());
	}

	public function testInline()
	{
		$html = '<div><h1>Headline</h1> <p>Subtitle with <a href="#">link</a>.</p></div>';
		$expected = 'Headline Subtitle with <a href="#">link</a>.';

		$this->assertSame($expected, $this->field($html)->inline()->value());
		$this->assertSame('', $this->field(null)->inline()->value());
	}

	public function testNl2br()
	{
		$input = 'Multiline' . PHP_EOL . 'test' . PHP_EOL . 'string';
		$expected = 'Multiline<br>' . PHP_EOL . 'test<br>' . PHP_EOL . 'string';

		$this->assertSame($expected, $this->field($input)->nl2br()->value());
		$this->assertSame('', $this->field(null)->nl2br()->value());
	}

	public function testKirbytext()
	{
		$kirbytext = '(link: # text: Test)';
		$expected  = '<p><a href="#">Test</a></p>';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytext()->value());
		$this->assertSame($expected, $this->field($kirbytext)->kt()->value());
	}

	public function testKirbytextWithSafeMode()
	{
		$kirbytext = '<h1>Test</h1>';
		$expected  = '<p>&lt;h1&gt;Test&lt;/h1&gt;</p>';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytext(['markdown' => ['safe' => true]])->value());
	}

	public function testKirbytextInline()
	{
		$kirbytext = '(link: # text: Test)';
		$expected  = '<a href="#">Test</a>';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytextinline()->value());
		$this->assertSame($expected, $this->field($kirbytext)->kti()->value());
	}

	public function testKirbytextInlineWithSafeMode()
	{
		$kirbytext = '<b>Test</b>';
		$expected  = '&lt;b&gt;Test&lt;/b&gt;';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytextInline(['markdown' => ['safe' => true]])->value());
	}

	public function testKirbytags()
	{
		$kirbytext = '(link: # text: Test)';
		$expected  = '<a href="#">Test</a>';

		$this->assertSame($expected, $this->field($kirbytext)->kirbytags()->value());
	}

	public function testLower()
	{
		$this->assertSame('abc', $this->field('ABC')->lower()->value());
	}

	public function testMarkdown()
	{
		$markdown = '**Test**';
		$expected = '<p><strong>Test</strong></p>';

		$this->assertSame($expected, $this->field($markdown)->markdown()->value());
	}

	public function testMarkdownWithSafeMode()
	{
		$markdown = '<h1>Test</h1>';
		$expected = '<p>&lt;h1&gt;Test&lt;/h1&gt;</p>';

		$this->assertSame($expected, $this->field($markdown)->markdown(['safe' => true])->value());
	}

	public function testOr()
	{
		$this->assertSame('field value', $this->field('field value')->or('fallback')->value());
		$this->assertSame('fallback', $this->field()->or('fallback')->value());
	}

	public function testQuery()
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

	public function testReplace()
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

	public function testShort()
	{
		$this->assertSame('abc…', $this->field('abcd')->short(3)->value());
	}

	public function testSmartypants()
	{
		$text     = '"Test"';
		$expected = '&#8220;Test&#8221;';

		$this->assertSame($expected, $this->field($text)->smartypants()->value());
	}

	public function testSmartypantsWithKirbytext()
	{
		new App([
			'roots' => [
				'index' => $this->tmp
			],
			'options' => [
				'smartypants' => true
			]
		]);

		$text     = '"Test"';
		$expected = '&#8220;Test&#8221;';

		$this->assertSame($expected, $this->field($text)->kti()->value());
	}

	public function testSlug()
	{
		$text     = 'Ä--Ö--Ü';
		$expected = 'a-o-u';

		$this->assertSame($expected, $this->field($text)->slug()->value());
	}

	public function testSplit()
	{
		$text = 'a, b, c';
		$expected = ['a', 'b', 'c'];

		$this->assertSame($expected, $this->field($text)->split());
	}

	public function testUpper()
	{
		$this->assertSame('ABC', $this->field('abc')->upper()->value());
	}

	public function testWidont()
	{
		$this->assertSame('Test&nbsp;Headline', $this->field('Test Headline')->widont()->value());
		$this->assertSame('Test Headline&nbsp;With&#8209;Dash', $this->field('Test Headline With-Dash')->widont()->value());
	}

	public function testWords()
	{
		$text = 'this is an example text';
		$this->assertSame(5, $this->field($text)->words());
		$this->assertSame(0, $this->field(null)->words());
	}

	public function testXml()
	{
		$this->assertSame('&#246;&#228;&#252;', $this->field('öäü')->xml()->value());
	}

	public function testYaml()
	{
		$data = [
			'a',
			'b',
			'c'
		];

		$yaml = Yaml::encode($data);
		$this->assertSame($data, $this->field($yaml)->yaml());
	}

	public function testToBlocks()
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
		$field  = new Field($page, 'test', $json);
		$blocks = $field->toBlocks();

		$this->assertInstanceOf(Blocks::class, $blocks);
		$this->assertInstanceOf(Page::class, $blocks->parent());
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

	public function testToBlocksWithInvalidData()
	{
		$data = [
			[
				'content' => [
					'text' => 'foo',
				]
			]
		];

		$json   = Json::encode($data);
		$field  = new Field(kirby()->page('files'), 'test', $json);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid blocks data for "test" field on parent "files"');

		$field->toBlocks();
	}

	public function testToLayouts()
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
		$field   = new Field($page, 'test', json_encode($data));
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

	public function testToObject()
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

	protected function field($value = '', $parent = null)
	{
		return new Field($parent, 'test', $value);
	}
}
