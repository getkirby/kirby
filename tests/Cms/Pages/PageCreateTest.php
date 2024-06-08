<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use TypeError;

class UncreatablePage extends Page
{
	public static function create(array $props): static
	{
		return 'the model was used';
	}
}

class PageCreateTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageCreate';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$this->app->impersonate('kirby');

		Dir::make(static::TMP);

		Page::$models = [
			'uncreatable-page' => UncreatablePage::class
		];
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);

		Page::$models = [];
	}

	public function testCreateDraft()
	{
		$site = $this->app->site();
		$page = Page::create([
			'slug' => 'new-page',
		]);

		$this->assertTrue($page->exists());
		$this->assertIsPage($page);
		$this->assertTrue($page->isDraft());
		$this->assertTrue($page->parentModel()->drafts()->has($page));
		$this->assertTrue($site->drafts()->has($page));
	}

	public function testCreateDraftWithDefaults()
	{
		$site = $this->app->site();
		$page = Page::create([
			'slug' => 'new-page',
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'a'  => [
						'type'    => 'text',
						'default' => 'A'
					],
					'b' => [
						'type'    => 'textarea',
						'default' => 'B'
					]
				]
			]
		]);

		$this->assertSame('A', $page->a()->value());
		$this->assertSame('B', $page->b()->value());
	}

	public function testCreateDraftWithDefaultsAndContent()
	{
		$site = $this->app->site();
		$page = Page::create([
			'content' => [
				'a' => 'Custom A'
			],
			'slug' => 'new-page',
			'blueprint' => [
				'name'   => 'test',
				'fields' => [
					'a'  => [
						'type'    => 'text',
						'default' => 'A'
					],
					'b' => [
						'type'    => 'textarea',
						'default' => 'B'
					]
				]
			]
		]);

		$this->assertSame('Custom A', $page->a()->value());
		$this->assertSame('B', $page->b()->value());
	}

	public function testCreateListedPage()
	{
		$site = $this->app->site();
		$page = Page::create([
			'slug' => 'new-page',
			'num'  => 1
		]);

		$this->assertTrue($page->exists());
		$this->assertIsPage($page);
		$this->assertFalse($page->isDraft());
		$this->assertTrue($page->parentModel()->children()->has($page));
		$this->assertTrue($site->children()->has($page));
	}

	public function testCreateUnlistedPageDraftProp()
	{
		$site = $this->app->site();
		$page = Page::create([
			'slug'  => 'new-page',
			'draft' => false,
		]);

		$this->assertTrue($page->exists());
		$this->assertIsPage($page);
		$this->assertFalse($page->isDraft());
		$this->assertFalse($page->isListed());
		$this->assertTrue($page->parentModel()->children()->has($page));
		$this->assertTrue($site->children()->has($page));
	}

	public function testCreateUnlistedPageIsDraftProp()
	{
		$site = $this->app->site();
		$page = Page::create([
			'slug'    => 'new-page',
			'draft'   => true,
			'isDraft' => false,
		]);

		$this->assertTrue($page->exists());
		$this->assertIsPage($page);
		$this->assertFalse($page->isDraft());
		$this->assertFalse($page->isListed());
		$this->assertTrue($page->parentModel()->children()->has($page));
		$this->assertTrue($site->children()->has($page));
	}

	public function testCreateDuplicate()
	{
		$this->expectException(DuplicateException::class);

		$page = Page::create([
			'slug' => 'new-page',
		]);

		$page = Page::create([
			'slug' => 'new-page',
		]);
	}

	public function testCreateChild()
	{
		Dir::make($this->app->root('content'));

		$mother = Page::create([
			'slug' => 'mother'
		]);

		$child = $mother->createChild([
			'slug'     => 'child',
			'template' => 'the-template'
		]);

		$this->assertTrue($child->exists());
		$this->assertSame('the-template', $child->intendedTemplate()->name());
		$this->assertSame('child', $child->slug());
		$this->assertSame('mother/child', $child->id());
		$this->assertTrue($mother->drafts()->has($child->id()));
	}

	public function testCreateChildCustomModel()
	{
		$mother = Page::create([
			'slug' => 'mother'
		]);

		try {
			$mother->createChild([
				'slug'     => 'child',
				'template' => 'uncreatable-page'
			]);
		} catch (TypeError) {
		}

		$this->assertTrue($mother->drafts()->isEmpty());
	}

	public function testCreateFile()
	{
		F::write($source = static::TMP . '/source.md', '');

		$page = Page::create([
			'slug' => 'test'
		]);

		$file = $page->createFile([
			'filename' => 'test.md',
			'source'   => $source
		]);

		$this->assertSame('test.md', $file->filename());
		$this->assertSame('test/test.md', $file->id());
	}
}
