<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Exception\DuplicateException;
use Kirby\Filesystem\Dir;
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
class NewPageCreateTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageCreate';

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

	public function testCreateDraft(): void
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

	public function testCreateDraftWithDefaults(): void
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

	public function testCreateDraftWithDefaultsAndContent(): void
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

	public function testCreateChild(): void
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

	public function testCreateChildWithCustomModel(): void
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

	public function testCreateDuplicate(): void
	{
		$this->expectException(DuplicateException::class);

		$page = Page::create([
			'slug' => 'new-page',
		]);

		$page = Page::create([
			'slug' => 'new-page',
		]);
	}

	public function testCreateHooks(): void
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

	public function testCreateListedPage(): void
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

	public function testCreateUnlistedPageDraftProp(): void
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

	public function testCreateUnlistedPageIsDraftProp(): void
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

	public function testCreateWhenDefaultLanguageIsActive(): void
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		$value = [
			'title'    => 'Test page',
			'headline' => 'A headline',
			'text'     => 'Any text'
		];

		Page::create([
			'slug'      => 'test',
			'content'   => $value,
			'blueprint' => [
				'title'  => 'Default',
				'fields' => [
					'headline' => ['type' => 'text'],
					'text'     => ['type' => 'textarea']
				]
			],
		]);

		$page = $this->app->page('test');

		$value['uuid'] = $page->content()->get('uuid')->value();

		$this->assertSame($value, $page->content('en')->toArray());
		$this->assertSame($value, $page->content('de')->toArray());
	}

	public function testCreateWhenSecondaryLanguageIsActive(): void
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');
		$this->app->setCurrentLanguage('de');

		$this->assertSame('de', $this->app->language()->code());

		$value = [
			'title'    => 'Test page',
			'headline' => 'A headline',
			'text'     => 'Any text'
		];

		Page::create([
			'slug'      => 'test',
			'content'   => $value,
			'blueprint' => [
				'title'  => 'Default',
				'fields' => [
					'headline' => ['type' => 'text'],
					'text'     => ['type' => 'textarea']
				]
			]
		]);

		$page = $this->app->page('test');

		$value['uuid'] = $page->content()->get('uuid')->value();

		$this->assertSame($value, $page->content('en')->toArray());
		$this->assertSame($value, $page->content('de')->toArray());
	}

	public function testCreateWhenSecondaryLanguageIsActiveAndThePageHasUntranslatableFields(): void
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');
		$this->app->setCurrentLanguage('de');

		$this->assertSame('de', $this->app->language()->code());

		$value = [
			'title'    => 'Test page',
			'headline' => 'A headline',
			'text'     => 'Any text'
		];

		Page::create([
			'slug'      => 'test',
			'content'   => $value,
			'blueprint' => [
				'title'  => 'Default',
				'fields' => [
					'headline' => [
						'type'      => 'text',
						'translate' => false
					],
					'text' => ['type' => 'textarea']
				]
			]
		]);

		$page = $this->app->page('test');

		$value['uuid'] = $page->content()->get('uuid')->value();

		$this->assertSame($value, $page->content('en')->toArray());
		$this->assertSame($value, $page->content('de')->toArray());
	}

	public function testCreateWhenSecondaryLanguageIsActiveAndThePageHasDefaultValues(): void
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');
		$this->app->setCurrentLanguage('de');

		$this->assertSame('de', $this->app->language()->code());

		Page::create([
			'slug'       => 'test',
			'content'    => ['title' => 'Test page'],
			'blueprint'  => [
				'title'  => 'test',
				'fields' => [
					'headline' => [
						'type'      => 'text',
						'translate' => false,
						'default'   => 'A headline'
					],
					'text'     => [
						'type'    => 'textarea',
						'default' => 'Any text'
					]
				]
			]
		]);

		$page = $this->app->page('test');

		$expected = [
			'title'    => 'Test page',
			'headline' => 'A headline',
			'text'     => 'Any text',
			'uuid'     =>  $page->content()->get('uuid')->value(),
		];

		$this->assertSame($expected, $page->content('en')->toArray());
		$this->assertSame($expected, $page->content('de')->toArray());
	}

	public function testCreateWithTranslations(): void
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		Page::create([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'en',
					'content' => [
						'title' => 'Title EN',
					]
				],
				[
					'code' => 'de',
					'content' => [
						'title' => 'Title DE',
					]
				],
			],
		]);

		$page = $this->app->page('test');

		$this->assertSame('Title EN', $page->content('en')->title()->value());
		$this->assertSame('Title DE', $page->content('de')->title()->value());
	}

	public function testCreateWithTranslationsAndContent(): void
	{
		$this->setupMultiLanguage();

		$this->app->impersonate('kirby');

		Page::create([
			'slug' => 'test',
			'content' => [
				'title' => 'Title EN',
			],
			'translations' => [
				[
					'code' => 'de',
					'content' => [
						'title' => 'Title DE',
					]
				],
			],
		]);

		$page = $this->app->page('test');

		$this->assertSame('Title EN', $page->content('en')->title()->value());
		$this->assertSame('Title DE', $page->content('de')->title()->value());
	}
}
