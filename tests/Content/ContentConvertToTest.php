<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Content\Content
 */
class ContentConvertToTest extends TestCase
{
	protected Content $content;
	protected ModelWithContent $parent;

	public function setUp(): void
	{
		$this->parent  = new Page(['slug' => 'test']);
		$this->content = new Content([
			'a' => 'A',
			'B' => 'B',
			'MiXeD' => 'mixed',
			'mIXeD' => 'MIXED'
		], $this->parent);
	}

	/**
	 * @covers ::convertTo
	 */
	public function testConvertToForPage()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'content-a',
						'content'  => [
							'stays'   => 'is there',
							'changes' => 'should go',
							'removed' => 'keep this'
						]
					]
				]
			],
			'blueprints' => [
				'pages/content-a' => [
					'fields' => [
						'stays' => [
							'type' => 'text'
						],
						'changes' => [
							'type' => 'text'
						],
						'removed' => [
							'type' => 'text'
						]
					]
				],
				'pages/content-b' => [
					'title'  => 'Article',
					'fields' => [
						'stays' => [
							'type' => 'text'
						],
						'changes' => [
							'type' => 'radio'
						]
					]
				]
			],
		]);

		$page    = $app->page('test');
		$content = $page->content();

		$this->assertTrue($content->has('stays'));
		$this->assertSame('is there', $content->get('stays')->value());
		$this->assertTrue($content->has('changes'));
		$this->assertSame('should go', $content->get('changes')->value());
		$this->assertTrue($content->has('removed'));
		$this->assertSame('keep this', $content->get('removed')->value());

		$new = $content->convertTo('content-b');

		$this->assertArrayHasKey('stays', $new);
		$this->assertSame('is there', $new['stays']);
		$this->assertArrayHasKey('changes', $new);
		$this->assertNull($new['changes']);
		$this->assertArrayHasKey('removed', $new);
		$this->assertSame('keep this', $new['removed']);
	}

	/**
	 * @covers ::convertTo
	 */
	public function testConvertToForFile()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'files' => [
					[
						'filename' => 'test.jpg',
						'content'  => [
							'alt'      => 'Test',
							'template' => 'content-a'
						]
					]
				]
			],
			'blueprints' => [
				'files/content-a' => [],
				'files/content-b' => []
			],
		]);

		$file    = $app->file('test.jpg');
		$content = $file->content();

		$new = $content->convertTo('content-b');

		$expected = [
			'alt'      => 'Test',
			'template' => 'content-b'
		];

		$this->assertSame($expected, $new, 'The template should be changed in the new content array');
	}
}
