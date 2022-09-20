<?php

namespace Kirby\Cms;

use Kirby\Uuid\PageUuid;
use Kirby\Uuid\SiteUuid;

class ExtendedModelWithContent extends ModelWithContent
{
	public function blueprint()
	{
		return 'test';
	}

	protected function commit(string $action, array $arguments, \Closure $callback)
	{
		// nothing to commit in the test
	}

	public function contentFileName(): string
	{
		return 'test.txt';
	}

	public function panel()
	{
		return new PageForPanel($this);
	}

	public function permissions()
	{
		return null;
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
	protected $testModel;

	public function __construct(Model $model)
	{
		$this->testModel = $model;
	}

	public function blueprint()
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
}

class ModelWithContentTest extends TestCase
{
	public function modelsProvider(): array
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

	public function testContentLock()
	{
		$model = new ExtendedModelWithContent();
		$this->assertInstanceOf('Kirby\\Cms\\ContentLock', $model->lock());
	}

	public function testContentLockWithNoDirectory()
	{
		$model = new BrokenModelWithContent();
		$this->assertNull($model->lock());
	}

	/**
	 * @dataProvider modelsProvider
	 * @param \Kirby\Cms\Model $model
	 */
	public function testBlueprints($model)
	{
		$model = new BlueprintsModelWithContent($model);
		$this->assertSame([
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
				'name' => 'Page',
				'title' => 'Page'
			]
		], $model->blueprints());

		$this->assertSame([
			[
				'name' => 'home',
				'title' => 'Home'
			],
			[
				'name' => 'Page',
				'title' => 'Page'
			]
		], $model->blueprints('menu'));
	}

	public function testToSafeString()
	{
		$model = new Page(['slug' => 'foo', 'content' => ['title' => 'value &']]);
		$this->assertSame('Hello value &amp; foo', $model->toSafeString('Hello {{ model.title }} {{ model.slug }}'));
		$this->assertSame('Hello value & foo', $model->toSafeString('Hello {< model.title >} {{ model.slug }}'));
	}

	public function testToSafeStringWithData()
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

	public function testToSafeStringWithFallback()
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

	public function testToString()
	{
		$model = new Site();
		$this->assertSame('Hello home', $model->toString('Hello {{ model.homePageId }}'));

		$model = new Page(['slug' => 'foo']);
		$this->assertSame('Hello foo/home', $model->toString('Hello {{ model.slug }}/{{ site.homePageId }}'));
	}

	public function testToStringWithData()
	{
		$model = new Site();
		$this->assertSame('Hello home in value', $model->toString('Hello {{ model.homePageId }} in {{ key }}', ['key' => 'value']));

		$model = new Page(['slug' => 'foo']);
		$this->assertSame(
			'Hello foo/home in value',
			$model->toString('Hello {{ model.slug }}/{{ site.homePageId }} in {{ key }}', ['key' => 'value'])
		);
	}

	public function testToStringWithFallback()
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

	public function testToStringWithoutValue()
	{
		$model = new Site();
		$this->assertSame('', $model->toString());

		$model = new Page(['slug' => 'foo']);
		$this->assertSame('foo', $model->toString());
	}

	public function testUuid()
	{
		$model = new Site();
		$this->assertInstanceOf(SiteUuid::class, $model->uuid());

		$model = new Page(['slug' => 'foo']);
		$this->assertInstanceOf(PageUuid::class, $model->uuid());
	}
}
