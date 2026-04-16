<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

class NewDefaultPage extends Page
{
}

class NewParentPage extends Page
{
}

class NewUncreatablePage extends Page
{
	public static function create(array $props): static
	{
		return 'the model was used';
	}
}

#[CoversClass(Page::class)]
class PageCreateTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageCreate';

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
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'name'   => 'test',
					'fields' => [
						'a' => [
							'type'    => 'text',
							'default' => 'A'
						],
						'b' => [
							'type'    => 'textarea',
							'default' => 'B'
						],
					]
				]
			]
		]);
		$this->app->impersonate('kirby');

		$page = Page::create([
			'slug'     => 'new-page',
			'template' => 'test',
		]);

		$this->assertSame('A', $page->a()->value());
		$this->assertSame('B', $page->b()->value());
	}

	public function testCreateDraftWithDefaultsAndContent(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'name'   => 'test',
					'fields' => [
						'a' => [
							'type'    => 'text',
							'default' => 'A'
						],
						'b' => [
							'type'    => 'textarea',
							'default' => 'B'
						],
					]
				]
			]
		]);
		$this->app->impersonate('kirby');

		$page = Page::create([
			'content'  => ['a' => 'Custom A'],
			'slug'     => 'new-page',
			'template' => 'test',
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
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'title'  => 'Default',
					'fields' => [
						'headline' => ['type' => 'text'],
						'text'     => ['type' => 'textarea'],
					]
				]
			]
		]);
		$this->app->impersonate('kirby');

		$value = [
			'title'    => 'Test page',
			'headline' => 'A headline',
			'text'     => 'Any text'
		];

		Page::create([
			'slug'    => 'test',
			'content' => $value,
		]);

		$page = $this->app->page('test');

		$value['uuid'] = $page->content()->get('uuid')->value();

		$this->assertSame($value, $page->content('en')->toArray());
		$this->assertSame($value, $page->content('de')->toArray());
	}

	public function testCreateWhenSecondaryLanguageIsActive(): void
	{
		$this->setupMultiLanguage();
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'title'  => 'Default',
					'fields' => [
						'headline' => ['type' => 'text'],
						'text'     => ['type' => 'textarea'],
					]
				]
			]
		]);
		$this->app->impersonate('kirby');
		$this->app->setCurrentLanguage('de');

		$this->assertSame('de', $this->app->language()->code());

		$value = [
			'title'    => 'Test page',
			'headline' => 'A headline',
			'text'     => 'Any text'
		];

		Page::create([
			'slug'    => 'test',
			'content' => $value,
		]);

		$page = $this->app->page('test');

		$value['uuid'] = $page->content()->get('uuid')->value();

		$this->assertSame($value, $page->content('en')->toArray());
		$this->assertSame($value, $page->content('de')->toArray());
	}

	public function testCreateWhenSecondaryLanguageIsActiveAndThePageHasUntranslatableFields(): void
	{
		$this->setupMultiLanguage();
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'title'  => 'Default',
					'fields' => [
						'headline' => ['type' => 'text',     'translate' => false],
						'text'     => ['type' => 'textarea'],
					]
				]
			]
		]);
		$this->app->impersonate('kirby');
		$this->app->setCurrentLanguage('de');

		$this->assertSame('de', $this->app->language()->code());

		$value = [
			'title'    => 'Test page',
			'headline' => 'A headline',
			'text'     => 'Any text'
		];

		Page::create([
			'slug'    => 'test',
			'content' => $value,
		]);

		$page = $this->app->page('test');

		$value['uuid'] = $page->content()->get('uuid')->value();

		$this->assertSame($value, $page->content('en')->toArray());
		$this->assertSame($value, $page->content('de')->toArray());
	}

	public function testCreateWhenSecondaryLanguageIsActiveAndThePageHasDefaultValues(): void
	{
		$this->setupMultiLanguage();
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/default' => [
					'title'  => 'test',
					'fields' => [
						'headline' => ['type' => 'text',     'translate' => false, 'default' => 'A headline'],
						'text'     => ['type' => 'textarea', 'default' => 'Any text'],
					]
				]
			]
		]);
		$this->app->impersonate('kirby');
		$this->app->setCurrentLanguage('de');

		$this->assertSame('de', $this->app->language()->code());

		Page::create([
			'slug'    => 'test',
			'content' => ['title' => 'Test page'],
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

	/**
	 * Issue: https://github.com/getkirby/kirby/issues/7084
	 */
	public function testCreateWithCustomModel(): void
	{
		$this->setupSingleLanguage();

		$this->app = $this->app->clone([
			'pageModels' => [
				'default' => NewDefaultPage::class,
				'parent'  => NewParentPage::class,
			]
		]);

		$this->app->impersonate('kirby');

		$parent = $this->app->site()->createChild([
			'slug'     => 'parent',
			'template' => 'parent',
		]);

		$child = $parent->createChild([
			'slug'     => 'test',
			'parent'   => $parent,
		]);

		$this->assertInstanceOf(NewParentPage::class, $parent);
		$this->assertInstanceOf(NewDefaultPage::class, $child);
	}

	public function testCreateStripInjectedBlueprint(): void
	{
		$this->app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'pages' => [
							'create' => false,
						]
					]
				]
			],
			'user'  => 'editor@domain.com',
			'users' => [
				['email' => 'editor@domain.com', 'role' => 'editor']
			]
		]);

		$this->expectException(PermissionException::class);

		Page::create([
			'slug'      => 'new-page',
			'blueprint' => [
				'options' => [
					// would allow creation if respected, must be stripped
					'create' => true
				]
			]
		]);
	}

	/**
	 * Changing status in page.create:after should not recreate the draft.
	 */
	public function testAfterCreateHookChangeStatusDoesNotDuplicateDraft(): void
	{
		$app = $this->app->clone([
			'hooks' => [
				'page.create:after' => function (Page $page) {
					$page->changeStatus('listed');
				}
			]
		]);

		$app->impersonate('kirby');

		Page::create(['slug' => 'test']);

		// the draft directory must not be recreated after being moved by publish()
		$this->assertDirectoryDoesNotExist(static::TMP . '/content/_drafts/test');

		// exactly one page should exist — no duplicate
		$this->assertCount(1, $app->site()->childrenAndDrafts());
		$this->assertCount(0, $app->site()->drafts());
		$this->assertCount(1, $app->site()->children());
	}

	/**
	 * Changing status in page.create:after should keep UUID cache valid.
	 */
	public function testAfterCreateHookChangeStatusKeepsUuidCacheValid(): void
	{
		$uuidWasCachedInAfterHook = false;

		$app = $this->app->clone([
			'hooks' => [
				'page.create:after' => function (Page $page) use (&$uuidWasCachedInAfterHook) {
					$uuidWasCachedInAfterHook = $page->uuid()->isCached();
					$page->changeStatus('listed');
				}
			]
		]);

		$app->impersonate('kirby');

		Page::create(['slug' => 'test']);

		$this->assertTrue($uuidWasCachedInAfterHook);

		// get the actual listed page
		$page = $app->site()->children()->first();

		// UUID cache must be populated with the listed page's UUID
		$this->assertTrue($page->uuid()->isCached());

		// resolving the cached UUID must return the same page
		$this->assertSame($page->id(), $page->uuid()->model()->id());
	}
}
