<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Panel\Page as Panel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TypeError;

class PageTestModel extends Page
{
}

#[CoversClass(Page::class)]
class PageTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.Page';

	public function testBlueprints()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/a' => [
					'title' => 'A'
				],
				'pages/b' => [
					'title' => 'B'
				],
				'pages/c' => [
					'title' => 'C'
				]
			],
			'templates' => [
				'a' => __FILE__,
				'c' => __FILE__
			]
		]);

		// no blueprints
		$page = new Page(['slug' => 'test', 'template' => 'a']);

		$this->assertSame(['A'], array_column($page->blueprints(), 'title'));

		// two different blueprints
		$page = new Page([
			'slug' => 'test',
			'template' => 'c',
			'blueprint' => [
				'options' => [
					'template' => [
						'a',
						'b'
					]
				]
			]
		]);

		$this->assertSame(['C', 'A', 'B'], array_column($page->blueprints(), 'title'));

		// including the same blueprint
		$page = new Page([
			'slug' => 'test',
			'template' => 'a',
			'blueprint' => [
				'options' => [
					'template' => [
						'a',
						'b'
					]
				]
			]
		]);

		$this->assertSame(['A', 'B'], array_column($page->blueprints(), 'title'));

		// template option is simply true
		$page = new Page([
			'slug' => 'test',
			'template' => 'a',
			'blueprint' => [
				'options' => [
					'template' => true
				]
			]
		]);

		$this->assertSame(['A'], array_column($page->blueprints(), 'title'));
	}

	public function testBlueprintsInSection()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/a' => [
					'title' => 'A',
					'sections' => [
						'my-pages' => [
							'type'   => 'pages',
							'create' => 'b'
						]
					]
				],
				'pages/b' => [
					'title' => 'B'
				]
			],
			'templates' => [
				'a' => __FILE__
			]
		]);

		// no blueprints
		$page = new Page(['slug' => 'test', 'template' => 'a']);
		$this->assertSame(['B'], array_column($page->blueprints('my-pages'), 'title'));
	}

	public function testDepth()
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'grandma',
					'children' => [
						[
							'slug' => 'mother',
							'children' => [
								[
									'slug' => 'child',
								]
							]
						]
					]
				]
			]
		]);

		$this->assertSame(1, $site->find('grandma')->depth());
		$this->assertSame(2, $site->find('grandma/mother')->depth());
		$this->assertSame(3, $site->find('grandma/mother/child')->depth());
	}

	public function testId()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->id());
	}

	public function testEmptyId()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The page slug is required');

		$page = new Page(['slug' => null]);
	}

	public function testErrors()
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'name' => 'test',
				'fields' => [
					'intro' => [
						'required' => true,
						'type'     => 'text'
					]
				]
			]
		]);

		$this->assertSame([
			'intro' => [
				'label' => 'Intro',
				'message' => [
					'required' => 'Please enter something'
				]
			]
		], $page->errors());
	}

	public function testErrorsWithoutBlueprint()
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame([], $page->errors());
	}

	public function testErrorsWithInfoSectionInBlueprint()
	{
		$page = new Page([
			'slug' => 'test',
			'blueprint' => [
				'name' => 'test',
				'sections' => [
					'info' => [
						'type'     => 'info',
						'headline' => 'Info',
						'text'     => 'info'
					]
				]
			]
		]);

		$this->assertSame([], $page->errors());
	}

	public function testInvalidId()
	{
		$this->expectException(TypeError::class);
		new Page(['slug' => []]);
	}

	public function testIsDraft()
	{
		$page = new Page([
			'slug'  => 'test',
			'num'   => 1
		]);

		$this->assertFalse($page->isDraft());

		$page = new Page([
			'slug'  => 'test',
			'num'   => null
		]);

		$this->assertFalse($page->isDraft());

		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true
		]);

		$this->assertTrue($page->isDraft());
	}

	public function testIsListed()
	{
		$page = new Page([
			'slug'  => 'test',
			'num'   => 1
		]);

		$this->assertTrue($page->isListed());

		$page = new Page([
			'slug'  => 'test',
			'num'   => null
		]);

		$this->assertFalse($page->isListed());

		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true
		]);

		$this->assertFalse($page->isListed());
	}

	public function testIsUnlisted()
	{
		$page = new Page([
			'slug'  => 'test',
			'num'   => 1
		]);

		$this->assertFalse($page->isUnlisted());

		$page = new Page([
			'slug'  => 'test',
			'num'   => null
		]);

		$this->assertTrue($page->isUnlisted());

		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true
		]);

		$this->assertFalse($page->isUnlisted());
	}

	public function testNum()
	{
		$page = new Page([
			'slug'  => 'test',
			'num' => 1
		]);

		$this->assertSame(1, $page->num());
	}

	public function testInvalidNum()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'  => 'test',
			'num'   => []
		]);
	}

	public function testEmptyNum()
	{
		$page = new Page([
			'slug'  => 'test',
			'num' => null
		]);

		$this->assertNull($page->num());
	}

	public function testParent()
	{
		$parent = new Page([
			'slug' => 'test'
		]);

		$page = new Page([
			'slug'     => 'test/child',
			'parent' => $parent
		]);

		$this->assertSame($parent, $page->parent());
	}

	public function testParentId()
	{
		$mother = new Page([
			'slug' => 'mother',
			'children' => [
				[
					'slug' => 'child'
				]
			]
		]);

		$this->assertNull($mother->parentId());
		$this->assertSame('mother', $mother->find('child')->parentId());
	}

	public function testParentPrevNext()
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug' => 'projects',
						'children' => [
							[
								'slug' => 'project-a',
							],
							[
								'slug' => 'project-b',
							]
						]
					],
					[
						'slug' => 'blog'
					]
				]
			]
		]);

		$child = $app->page('projects/project-a');
		$blog  = $app->page('blog');

		$this->assertSame($blog, $child->parent()->next());
		$this->assertNull($child->parent()->prev());
	}

	public function testInvalidParent()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'     => 'test/child',
			'parent' => 'some parent'
		]);
	}

	public function testSite()
	{
		$site = new Site();
		$page = new Page([
			'slug'   => 'test',
			'site' => $site
		]);

		$this->assertIsSite($site, $page->site());
	}

	public function testInvalidSite()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'   => 'test',
			'site' => 'mysite'
		]);
	}

	public function testDefaultTemplate()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertInstanceOf(Template::class, $page->template());
		$this->assertSame('default', $page->template()->name());
	}

	public function testIntendedTemplate()
	{
		$page = new Page([
			'slug'     => 'test',
			'template' => 'testTemplate'
		]);

		$this->assertSame('testtemplate', $page->intendedTemplate()->name());
	}

	public function testInvalidTemplate()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug'     => 'test',
			'template' => []
		]);
	}

	public function testUrl()
	{
		$page = new Page([
			'slug'  => 'test',
			'url' => 'https://getkirby.com/test'
		]);

		$this->assertSame('https://getkirby.com/test', $page->url());
	}

	public function testUrlWithOptions()
	{
		$page = new Page([
			'slug'  => 'test',
			'url' => 'https://getkirby.com/test'
		]);

		$this->assertSame('https://getkirby.com/test/foo:bar?q=search', $page->url([
			'params' => 'foo:bar',
			'query'  => 'q=search'
		]));
	}

	public function testDefaultUrl()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('/test', $page->url());
	}

	public function testInvalidUrl()
	{
		$this->expectException(TypeError::class);

		new Page([
			'slug' => 'test',
			'url'  => []
		]);
	}

	public function testHomeUrl()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		$this->assertSame('/', $app->site()->find('home')->url());
	}

	public function testHomeChildUrl()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'home',
						'children' => [
							['slug' => 'a']
						]
					]
				]
			]
		]);

		$this->assertSame('/home/a', $app->site()->find('home/a')->url());
	}

	public function testMultiLangHomeChildUrl()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				],
				[
					'code'    => 'de',
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'home',
						'children' => [
							['slug' => 'a']
						]
					]
				]
			]
		]);

		$this->assertSame('/en/home/a', $app->site()->find('home/a')->url());
		$this->assertSame('/de/home/a', $app->site()->find('home/a')->url('de'));
	}

	public function testPreviewUrl()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => '/'
			]
		]);

		$page = new Page([
			'slug' => 'test'
		]);

		// authenticate
		$app->impersonate('kirby');

		$this->assertSame('/test', $page->previewUrl());
	}

	public function testPreviewUrlUnauthenticated()
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => '/'
			]
		]);

		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertNull($page->previewUrl());
	}

	public static function previewUrlProvider(): array
	{
		return [
			[null, '/test', null, false],
			[null, '/test?{token}', 'test', true],
			[true, '/test', null, false],
			[true, '/test?{token}', 'test', true],
			['/something/different', '/something/different', null, false],
			['/something/different', '/something/different?{token}', 'something\/different', true],
			['{{ site.url }}#{{ page.slug }}', '/#test', null, false],
			['{{ site.url }}#{{ page.slug }}', '/?{token}#test', '', true],
			['{{ page.url }}?preview=true', '/test?preview=true&{token}', 'test', true],
			[false, null, null, false],
			[false, null, null, true],
			[null, null, null, false, false],
		];
	}

	#[DataProvider('previewUrlProvider')]
	public function testCustomPreviewUrl(
		bool|string|null $input,
		string|null $expected,
		string|null $expectedUri,
		bool $draft,
		bool $authenticated = true
	): void {
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => '/'
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'roles' => [
				[
					'id'    => 'editor',
					'name'  => 'editor',
				]
			]
		]);

		// authenticate
		if ($authenticated) {
			$app->impersonate('test@getkirby.com');
		}

		$options = [];

		if ($input !== null) {
			$options = [
				'preview' => $input
			];
		}

		// simple
		$page = new Page([
			'slug' => 'test',
			'isDraft' => $draft,
			'blueprint' => [
				'name'    => 'test',
				'options' => $options
			]
		]);

		if ($draft === true && $expected !== null) {
			$expectedToken = substr(hash_hmac('sha1', '{"uri":"' . $expectedUri . '","versionId":"latest"}', $page->kirby()->root('content')), 0, 10);
			$expected = str_replace(
				'{token}',
				'_token=' . $expectedToken,
				$expected
			);
		}

		$this->assertSame($expected, $page->previewUrl());
	}

	public function testSlug()
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->slug());
	}

	public function testToString()
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->toString('{{ page.slug }}'));
	}

	public function testUid()
	{
		$page = new Page(['slug' => 'test']);
		$this->assertSame('test', $page->uid());
	}

	public function testUri()
	{
		$site = new Site([
			'children' => [
				[
					'slug' => 'grandma',
					'children' => [
						[
							'slug' => 'mother',
							'children' => [
								[
									'slug' => 'child'
								]
							]
						]
					]
				]
			]
		]);

		$this->assertSame('grandma/mother/child', $site->find('grandma/mother/child')->uri());
	}

	public function testUriTranslated()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'languages' => [
				[
					'code' => 'en'
				],
				[
					'code' => 'de'
				],
			],
			'site' => [
				'children' => [
					[
						'slug' => 'grandma',
						'translations' => [
							[
								'code' => 'en',
							],
							[
								'code' => 'de',
								'slug' => 'oma'
							],
						],
						'children' => [
							[
								'slug' => 'mother',
								'translations' => [
									[
										'code' => 'en'
									],
									[
										'code' => 'de',
										'slug' => 'mutter'
									],
								],
							]
						]
					]
				]
			]
		]);


		$this->assertSame('grandma/mother', $app->site()->find('grandma/mother')->uri());
		$this->assertSame('oma/mutter', $app->site()->find('grandma/mother')->uri('de'));
	}

	public function testModified()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			]
		]);

		// create a page
		F::write($file = static::TMP . '/test/test.txt', 'test');

		$modified = filemtime($file);
		$page     = $app->page('test');

		$this->assertSame($modified, $page->modified());

		// default date handler
		$format = 'd.m.Y';
		$this->assertSame(date($format, $modified), $page->modified($format));

		// custom date handler without format
		$this->assertSame($modified, $page->modified(null, 'strftime'));

		// custom date handler with format
		$format = '%d.%m.%Y';
		$this->assertSame(@strftime($format, $modified), $page->modified($format, 'strftime'));
	}

	public function testModifiedInMultilangInstallation()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			]
		]);

		// create the english page
		F::write($file = static::TMP . '/test/test.en.txt', 'test');
		touch($file, $modified = \time() + 2);

		$this->assertSame($modified, $app->page('test')->modified());

		// create the german page
		F::write($file = static::TMP . '/test/test.de.txt', 'test');
		touch($file, $modified = \time() + 5);

		// change the language
		$app->setCurrentLanguage('de');
		$app->setCurrentTranslation('de');

		$this->assertSame($modified, $app->page('test')->modified());
	}

	public function testModifiedSpecifyingLanguage()
	{
		$app = new App([
			'roots' => [
				'index'   => static::TMP,
				'content' => static::TMP
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			]
		]);

		// create the english page
		F::write($file = static::TMP . '/test/test.en.txt', 'test');
		touch($file, $modifiedEnContent = \time() + 2);

		// create the german page
		F::write($file = static::TMP . '/test/test.de.txt', 'test');
		touch($file, $modifiedDeContent = \time() + 5);

		$page = $app->page('test');

		$this->assertSame($modifiedEnContent, $page->modified(null, null, 'en'));
		$this->assertSame($modifiedDeContent, $page->modified(null, null, 'de'));
	}

	public function testPanel()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertInstanceOf(Panel::class, $page->panel());
	}

	public function testApiUrl()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'mother',
						'children' => [
							[
								'slug' => 'child'
							]
						]
					]
				]
			]
		]);

		$page = $app->page('mother/child');

		$this->assertSame('https://getkirby.com/api/pages/mother+child', $page->apiUrl());
		$this->assertSame('pages/mother+child', $page->apiUrl(true));
	}

	public function testPageMethods()
	{
		Page::$methods = [
			'test' => fn () => 'homer'
		];

		$page = new Page(['slug' => 'test']);

		$this->assertSame('homer', $page->test());

		Page::$methods = [];
	}

	public function testPageModel()
	{
		Page::$models = [
			'dummy' => PageTestModel::class
		];

		$page = Page::factory([
			'slug'  => 'test',
			'model' => 'dummy'
		]);

		$this->assertInstanceOf(PageTestModel::class, $page);

		Page::$models = [];
	}

	public function testPermalink()
	{
		$page = Page::factory([
			'slug'    => 'test',
			'content' => ['uuid' => 'my-page-uuid']
		]);

		$this->assertSame('//@/page/my-page-uuid', $page->permalink());
	}

	public function testController()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'templates' => [
				'foo' => static::FIXTURES . '/PageTemplateTest/template.php',
				'bar' => static::FIXTURES . '/PageTemplateTest/template.php',
			],
			'site' => [
				'children' => [
					[
						'slug'      => 'foo',
						'template'  => 'foo',
						'content'   => [
							'title' => 'Foo Title',
						]
					],
					[
						'slug'      => 'bar',
						'template'  => 'bar',
						'content'   => [
							'title' => 'Bar Title',
						]
					]
				],
			],
			'controllers' => [
				// valid return
				'foo' => function ($page) {
					$page = $page->changeTitle('New Foo Title');

					return compact('page');
				},
				// invalid return
				'bar' => fn ($page) => ['page' => 'string']
			]
		]);

		$app->impersonate('kirby');

		// valid test
		$page = $app->page('foo');
		$data = $page->controller();

		$this->assertCount(4, $data);
		$this->assertSame($app, $data['kirby']);
		$this->assertSame($app->site(), $data['site']);
		$this->assertSame($app->site()->children(), $data['pages']);
		$this->assertIsPage($data['page']);
		$this->assertSame('New Foo Title', $data['page']->title()->value());

		// invalid test
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The returned variable "page" from the controller "bar" is not of the required type "Kirby\Cms\Page"');

		$page = $app->page('bar');
		$page->controller();
	}

	public function testQuery()
	{
		$page = new Page([
			'slug' => 'test'
		]);

		$this->assertSame('test', $page->query('page.slug'));
		$this->assertSame('test', $page->query('model.slug'));
	}

	public function testToArray()
	{
		$this->app->clone([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
		$page = new Page([
			'slug' => 'test'
		]);

		$expected = [
			'content' => [],
			'translations' => [],
			'children' => [],
			'files' => [],
			'id' => 'test',
			'mediaUrl' => '/media/pages/test',
			'mediaRoot' => '/dev/null/media/pages/test',
			'num' => null,
			'parent' => null,
			'slug' => 'test',
			'template' => $page->template(),
			'uid' => 'test',
			'uri' => 'test',
			'url' => '/test',
		];

		$this->assertSame($expected, $page->toArray());
	}
}
