<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\Lock;
use Kirby\Content\Version;
use Kirby\Content\VersionId;
use Kirby\Content\Versions;
use Kirby\Exception\NotFoundException;
use Kirby\Panel\Page as PanelPage;
use Kirby\Uuid\PageUuid;
use Kirby\Uuid\SiteUuid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

class ExtendedModelWithContent extends ModelWithContent
{
	public function blueprint(): Blueprint
	{
		return new Blueprint([]);
	}

	protected function commit(
		string $action,
		array $arguments,
		Closure $callback
	): mixed {
		// nothing to commit in the test
	}

	public function panel(): PanelPage
	{
		return new PanelPage($this);
	}

	public function permissions(): ModelPermissions
	{
		return new ModelPermissions($this);
	}

	public function root(): string|null
	{
		return '/tmp';
	}
}

class BrokenModelWithContent extends ExtendedModelWithContent
{
	public function root(): string|null
	{
		return null;
	}
}

class BlueprintsModelWithContent extends ExtendedModelWithContent
{
	public function __construct(
		protected ModelWithContent $testModel
	) {
	}

	public function blueprint(): Blueprint
	{
		return new Blueprint([
			'model'  => $this->testModel,
			'name'   => 'model',
			'title'  => 'Model',
			'columns' => [
				[
					'sections' => [
						'pages' => [
							'name' => 'pages',
							'type' => 'pages',
							'parent' => 'site',
							'templates' => [
								'foo',
								'bar',
							]
						],
						'menu' => [
							'name' => 'menu',
							'type' => 'pages',
							'parent' => 'site',
							'templates' => [
								'home',
								'default',
							]
						]
					]
				]
			]
		]);
	}

	public function blueprintsCache(): array|null
	{
		return $this->blueprints;
	}
}

#[CoversClass(ModelWithContent::class)]
class ModelWithContentTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.ModelWithContent';

	public static function modelsProvider(): array
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug'  => 'foo',
						'files' => [
							['filename' => 'a.jpg'],
							['filename' => 'b.jpg']
						]
					]
				],
				'files' => [
					['filename' => 'c.jpg']
				]
			],
			'users' => [
				[
					'email' => 'test@getkirby.com'
				]
			]
		]);

		return [
			[$app->site()],
			[$app->page('foo')],
			[$app->site()->files()->first()],
			[$app->user('test@getkirby.com')]
		];
	}

	public function testContentForInvalidTranslation(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'languages' => true
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'foo',
					]
				],
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid language: fr');

		$app->page('foo')->content('fr');
	}

	public function testContentUpdate(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'foo',
					]
				],
			]
		]);

		$page = $app->page('foo');

		// update the content of the current language
		$this->assertNull($page->content()->get('title')->value());

		$page->version()->save(['title' => 'Test']);

		$this->assertSame('Test', $page->content()->get('title')->value());
	}

	public function testContentUpdateWithMultipleLanguages(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'languages' => true
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'foo',
					]
				],
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		$page = $app->page('foo');

		// update the content of the current language
		$this->assertNull($page->content()->get('title')->value());

		$page->version()->save(['title' => 'Test']);

		$this->assertSame('Test', $page->content()->get('title')->value());
	}

	public function testContentWithChanges(): void
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'foo',
					]
				],
			]
		]);

		$page = $app->page('foo');

		// create the latest version
		$page->version('latest')->save([
			'title' => 'Original Title'
		]);

		$this->assertSame('Original Title', $page->content()->title()->value());

		// create some changes
		$page->version('changes')->save([
			'title' => 'Changed Title'
		]);

		VersionId::$render = VersionId::changes();

		$this->assertSame('Changed Title', $page->content()->title()->value());

		VersionId::$render = null;

		$this->assertSame('Original Title', $page->content()->title()->value());
	}

	#[DataProvider('modelsProvider')]
	public function testBlueprints(ModelWithContent $model): void
	{
		$model = new BlueprintsModelWithContent($model);

		$expected = [
			[
				'name' => 'foo',
				'title' => 'Foo'
			],
			[
				'name' => 'bar',
				'title' => 'Bar'
			],
			[
				'name' => 'home',
				'title' => 'Home'
			],
			[
				'name' => 'default',
				'title' => 'Page'
			]
		];

		$this->assertSame($expected, $model->blueprints());

		$this->assertSame([
			[
				'name' => 'home',
				'title' => 'Home'
			],
			[
				'name' => 'default',
				'title' => 'Page'
			]
		], $model->blueprints('menu'));

		// non-existing section
		$this->assertSame([], $model->blueprints('foo'));
	}

	#[DataProvider('modelsProvider')]
	public function testIsValid(ModelWithContent $model): void
	{
		$this->assertTrue($model->isValid());
	}

	#[DataProvider('modelsProvider')]
	public function testIsValidWithErrors(ModelWithContent $model): void
	{
		// we can only test this with a real model
		$model = $model->clone([
			'blueprint' => [
				'fields' => [
					'test' => [
						'required' => true,
						'type' => 'text'
					]
				]
			]
		]);

		$this->assertFalse($model->isValid());
	}

	public function testKirby(): void
	{
		$kirby = new App();
		$model = new Page([
			'slug'  => 'foo',
			'kirby' => $kirby
		]);
		$this->assertSame($kirby, $model->kirby());
	}

	public function testLock(): void
	{
		$page = new Page(['slug' => 'foo']);
		$lock = $page->lock();

		$this->assertInstanceOf(Lock::class, $lock);
		$this->assertFalse($lock->isLocked());
		$this->assertNull($lock->modified());
		$this->assertNull($lock->user());
	}

	#[DataProvider('modelsProvider')]
	public function testPurge(ModelWithContent $model): void
	{
		$model = new BlueprintsModelWithContent($model);

		$this->assertNull($model->blueprintsCache());

		$expected = [
			[
				'name' => 'foo',
				'title' => 'Foo'
			],
			[
				'name' => 'bar',
				'title' => 'Bar'
			],
			[
				'name' => 'home',
				'title' => 'Home'
			],
			[
				'name' => 'default',
				'title' => 'Page'
			]
		];

		// fill the cache
		$model->blueprints();

		$this->assertSame($expected, $model->blueprintsCache());

		$model->purge();

		$this->assertNull($model->blueprintsCache());
	}

	public function testSite(): void
	{
		$site  = new Site();
		$model = new Page([
			'slug' => 'foo',
			'site' => $site
		]);
		$this->assertIsSite($site, $model->site());
	}

	public function testToSafeString(): void
	{
		$model = new Page(['slug' => 'foo', 'content' => ['title' => 'value &']]);
		$this->assertSame('Hello value &amp; foo', $model->toSafeString('Hello {{ model.title }} {{ model.slug }}'));
		$this->assertSame('Hello value & foo', $model->toSafeString('Hello {< model.title >} {{ model.slug }}'));
	}

	public function testToSafeStringWithData(): void
	{
		$model = new Site();
		$this->assertSame(
			'Hello home in value &amp; value',
			$model->toSafeString('Hello {{ model.homePageId }} in {{ key }}', ['key' => 'value & value'])
		);
		$this->assertSame(
			'Hello home in value & value',
			$model->toSafeString('Hello {{ model.homePageId }} in {< key >}', ['key' => 'value & value'])
		);

		$model = new Page(['slug' => 'foo']);
		$this->assertSame(
			'Hello foo/home in value &amp; value',
			$model->toSafeString('Hello {{ model.slug }}/{{ site.homePageId }} in {{ key }}', ['key' => 'value & value'])
		);
		$this->assertSame(
			'Hello foo/home in value & value',
			$model->toSafeString('Hello {{ model.slug }}/{{ site.homePageId }} in {< key >}', ['key' => 'value & value'])
		);
	}

	public function testToSafeStringWithFallback(): void
	{
		$model = new Site();
		$this->assertSame('Hello ', $model->toSafeString('Hello {{ invalid }}', []));
		$this->assertSame('Hello world', $model->toSafeString('Hello {{ invalid }}', [], 'world'));
		$this->assertSame('Hello {{ invalid }}', $model->toSafeString('Hello {{ invalid }}', [], null));

		$model = new Page(['slug' => 'foo']);
		$this->assertSame('Hello foo/', $model->toSafeString('Hello {{ model.slug }}/{{ invalid }}', []));
		$this->assertSame('Hello foo/world', $model->toSafeString('Hello {{ model.slug }}/{{ invalid }}', [], 'world'));
		$this->assertSame('Hello foo/{{ invalid }}', $model->toSafeString('Hello {{ model.slug }}/{{ invalid }}', [], null));
	}

	public function testToString(): void
	{
		$model = new Site();
		$this->assertSame('Hello home', $model->toString('Hello {{ model.homePageId }}'));

		$model = new Page(['slug' => 'foo']);
		$this->assertSame('Hello foo/home', $model->toString('Hello {{ model.slug }}/{{ site.homePageId }}'));
	}

	public function testToStringWithData(): void
	{
		$model = new Site();
		$this->assertSame('Hello home in value', $model->toString('Hello {{ model.homePageId }} in {{ key }}', ['key' => 'value']));

		$model = new Page(['slug' => 'foo']);
		$this->assertSame(
			'Hello foo/home in value',
			$model->toString('Hello {{ model.slug }}/{{ site.homePageId }} in {{ key }}', ['key' => 'value'])
		);
	}

	public function testToStringWithFallback(): void
	{
		$model = new Site();
		$this->assertSame('Hello ', $model->toString('Hello {{ invalid }}', []));
		$this->assertSame('Hello world', $model->toString('Hello {{ invalid }}', [], 'world'));
		$this->assertSame('Hello {{ invalid }}', $model->toString('Hello {{ invalid }}', [], null));

		$model = new Page(['slug' => 'foo']);
		$this->assertSame('Hello foo/', $model->toString('Hello {{ model.slug }}/{{ invalid }}', []));
		$this->assertSame('Hello foo/world', $model->toString('Hello {{ model.slug }}/{{ invalid }}', [], 'world'));
		$this->assertSame('Hello foo/{{ invalid }}', $model->toString('Hello {{ model.slug }}/{{ invalid }}', [], null));
	}

	public function testToStringWithoutValue(): void
	{
		$model = new Site();
		$this->assertSame('', $model->toString());

		$model = new Page(['slug' => 'foo']);
		$this->assertSame('foo', $model->toString());
	}

	public function testTranslation(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'languages' => true
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'foo',
						'translations' => [
							[
								'code' => 'en',
								'content' => [
									'title' => 'English Title'
								]
							],
							[
								'code' => 'de',
								'content' => [
									'title' => 'Deutscher Titel'
								]
							]
						]
					]
				],
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		$app->setCurrentLanguage('de');

		$en = $app->page('foo')->translation('en');
		$this->assertSame('English Title', $en->content()['title']);

		$de = $app->page('foo')->translation('de');
		$this->assertSame('Deutscher Titel', $de->content()['title']);

		$default = $app->page('foo')->translation('default');
		$this->assertSame('English Title', $default->content()['title']);

		$current = $app->page('foo')->translation();
		$this->assertSame('Deutscher Titel', $current->content()['title']);
	}

	public function testTranslationWithInvalidLanguauge(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'languages' => true
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'foo',
					]
				],
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid language: fr');

		$fr = $app->page('foo')->translation('fr');
	}

	public function testUuid(): void
	{
		$model = new Site();
		$this->assertInstanceOf(SiteUuid::class, $model->uuid());

		$model = new Page(['slug' => 'foo']);
		$this->assertInstanceOf(PageUuid::class, $model->uuid());
	}

	public function testVersion(): void
	{
		$model = new Site();
		$this->assertInstanceOf(Version::class, $model->version('latest'));
		$this->assertSame('latest', $model->version('latest')->id()->value());
		$this->assertSame('latest', $model->version(VersionId::latest())->id()->value());

		$model = new Page(['slug' => 'foo']);
		$this->assertInstanceOf(Version::class, $model->version('latest'));
		$this->assertSame('latest', $model->version('latest')->id()->value());
		$this->assertSame('latest', $model->version(VersionId::latest())->id()->value());
	}

	public function testVersionFallback(): void
	{
		$model = new Page(['slug' => 'foo']);
		$this->assertInstanceOf(Version::class, $model->version());
		$this->assertSame('latest', $model->version()->id()->value());
	}

	public function testVersions(): void
	{
		$model    = new Page(['slug' => 'test']);
		$versions = $model->versions();

		$this->assertInstanceOf(Versions::class, $versions);
		$this->assertCount(2, $versions);
	}
}
