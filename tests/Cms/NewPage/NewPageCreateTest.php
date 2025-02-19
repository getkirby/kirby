<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Exception\DuplicateException;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

class NewUncreatablePage extends Page
{
	public static function create(array $props): static
	{
		return 'the model was used';
	}
}

#[CoversClass(Page::class)]
class NewPageCreateTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageCreateTest';

	public function setUp(): void
	{
		parent::setUp();

		Page::$models = [];
	}

	public function tearDown(): void
	{
		parent::tearDown();

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

	public function testCreateChildWithCustomModel()
	{
		Page::$models['uncreatable-page'] = NewUncreatablePage::class;

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

	public function testCreateHooks()
	{
		$calls = 0;
		$phpunit = $this;

		$app = $this->app->clone([
			'hooks' => [
				'page.create:before' => function (Page $page, $input) use ($phpunit, &$calls) {
					$phpunit->assertIsPage($page);
					$phpunit->assertSame('test', $input['slug']);
					$phpunit->assertSame('default', $input['model']);
					$phpunit->assertSame('default', $input['template']);
					$phpunit->assertTrue($input['isDraft']);
					$phpunit->assertArrayHasKey('uuid', $input['content']);
					$calls++;
				},
				'page.create:after' => function (Page $page) use ($phpunit, &$calls) {
					$phpunit->assertIsPage($page);
					$phpunit->assertSame('test', $page->slug());
					$calls++;
				}
			]
		]);

		$app->impersonate('kirby');

		Page::create([
			'slug' => 'test',
		]);

		$this->assertSame(2, $calls);
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

}
