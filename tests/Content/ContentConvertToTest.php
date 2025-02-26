<?php

namespace Kirby\Content;

use PHPUnit\Attributes\CoversClass;

#[CoversClass(Content::class)]
class ContentConvertToTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->setUpSingleLanguage();
	}

	public function testConvertToForPage()
	{
		$app = $this->app->clone([
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

	public function testConvertToForFile()
	{
		$app = $this->app->clone([
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
