<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PageRules::class)]
class PageRulesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageRules';

	public function appWithAdmin()
	{
		return new App([
			'user' => 'test@getkirby.com',
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			],
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function testChangeNum()
	{
		$page = new Page([
			'slug'  => 'test',
			'kirby' => $this->appWithAdmin(),
		]);

		$this->expectNotToPerformAssertions();

		PageRules::changeNum($page, 2);
		PageRules::changeNum($page);
	}

	public function testInvalidChangeNum()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.num.invalid');

		$page = new Page([
			'slug'  => 'test',
			'kirby' => $this->appWithAdmin(),
		]);

		PageRules::changeNum($page, -1);
	}

	public function testChangeSlug()
	{
		$app = $this->appWithAdmin()->clone([
			'site' => [
				'children' => [
					['slug' => 'test-b'],
				]
			]
		]);

		$page = new Page([
			'slug'  => 'test',
			'kirby' => $app,
		]);

		PageRules::changeSlug($page, 'test-a');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A page with the URL appendix "test-b" already exists');

		PageRules::changeSlug($page, 'test-b');
	}

	public function testChangeSlugWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('changeSlug')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the URL appendix for "test"');

		PageRules::changeSlug($page, 'test');
	}

	public function testChangeSlugWithHomepage()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeSlug.permission');

		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		$app->impersonate('kirby');

		PageRules::changeSlug($app->page('home'), 'test-a');
	}

	public function testChangeSlugWithErrorPage()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeSlug.permission');

		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					['slug' => 'error']
				]
			]
		]);

		$app->impersonate('kirby');

		PageRules::changeSlug($app->page('error'), 'test-a');
	}

	public function testChangeSlugReservedPath()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeSlug.reserved');

		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$app->impersonate('kirby');

		PageRules::changeSlug($app->page('a'), 'api');
	}

	public static function statusActionProvider(): array
	{
		return [
			['draft'],
			['listed', [1]],
			['unlisted'],
		];
	}

	#[DataProvider('statusActionProvider')]
	public function testChangeStatusWithoutPermission(string $status, array $args = [])
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('changeStatus')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The status for this page cannot be changed');

		PageRules::{'changeStatusTo' . $status}($page, ...$args);
	}

	public function testChangeStatusToListedWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('changeStatus')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The status for this page cannot be changed');

		PageRules::changeStatusToDraft($page);
	}

	public function testChangeStatusInvalid()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.changeStatus.toDraft.invalid');

		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		$app->impersonate('kirby');

		PageRules::changeStatusToDraft($app->page('home'));
	}

	#[DataProvider('statusActionProvider')]
	public function testChangeStatus(string $status, array $args = [])
	{
		$app = $this->appWithAdmin()->clone([
			'site' => [
				'children' => [
					['slug' => 'test'],
				]
			]
		]);

		$this->expectNotToPerformAssertions();

		$page = new Page([
			'slug'  => 'test-' . $status,
			'kirby' => $app,
		]);

		PageRules::changeStatus($page, $status, ...$args);
	}

	public function testChangeTemplate()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'templates' => [
				'a' => __FILE__,
				'b' => __FILE__
			],
			'blueprints' => [
				'pages/a' => ['title' => 'a'],
				'pages/b' => ['title' => 'b'],
			]
		]);

		$app->impersonate('kirby');

		$page = new Page([
			'kirby' => $app,
			'slug'  => 'test',
			'template' => 'a',
			'blueprint' => [
				'name' => 'a',
				'options' => [
					'template' => [
						'a',
						'b'
					]
				]
			]
		]);

		$this->expectNotToPerformAssertions();

		PageRules::changeTemplate($page, 'b');
	}

	public function testChangeTemplateWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('changeTemplate')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the template for "test"');

		PageRules::changeTemplate($page, 'test');
	}

	public function testChangeTemplateTooFewTemplates()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('changeTemplate')->willReturn(true);

		$page = $this->createMock(Page::class);
		$page->method('blueprints')->willReturn([[]]);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The template for the page "test" cannot be changed');

		PageRules::changeTemplate($page, 'c');
	}

	public function testChangeTemplateWithInvalidTemplateName()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('changeTemplate')->willReturn(true);

		$page = $this->createMock(Page::class);
		$page->method('blueprints')->willReturn([
			['name' => 'a'], ['name' => 'b']
		]);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The template for the page "test" cannot be changed');

		PageRules::changeTemplate($page, 'c');
	}

	public function testChangeTitleWithEmptyValue()
	{
		$page = new Page([
			'slug'  => 'test',
			'kirby' => $this->appWithAdmin(),
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		PageRules::changeTitle($page, '');
	}

	public function testChangeTitleWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('changeTitle')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change the title for "test"');

		PageRules::changeTitle($page, 'test');
	}

	public function testCreateWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('create')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create "test"');

		PageRules::create($page);
	}

	public function testCreateInvalidSlug()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('create')->willReturn(true);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		PageRules::create($page);
	}

	public function testCreateDuplicateException()
	{
		$app = $this->appWithAdmin()->clone([
			'site' => [
				'children' => [
					['slug' => 'test'],
				]
			]
		]);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.page.duplicate');

		$page = new Page([
			'slug'  => 'test',
			'kirby' => $app,
		]);

		PageRules::create($page);
	}

	public function testCreateSlugReservedPath()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeSlug.reserved');

		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			]
		]);

		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('create')->willReturn(true);

		$page = $this->createMock(Page::class);
		$page->method('kirby')->willReturn($app);
		$page->method('permissions')->willReturn($permissions);
		$page->method('slug')->willReturn('api');

		PageRules::create($page);
	}

	public function testDelete()
	{
		$page = new Page([
			'kirby' => $this->appWithAdmin(),
			'slug'  => 'test',
		]);

		$this->expectNotToPerformAssertions();

		PageRules::delete($page);
	}

	public function testDeleteWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('delete')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete "test"');

		PageRules::delete($page);
	}

	public function testDeleteNotExists()
	{
		$page = new Page([
			'kirby' => $this->appWithAdmin(),
			'slug'  => 'test',
		]);

		$this->expectNotToPerformAssertions();

		PageRules::delete($page);
	}

	public function testDeleteHomepage()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.delete.permission');

		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		$app->impersonate('kirby');

		PageRules::delete($app->page('home'));
	}

	public function testDeleteErrorPage()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.delete.permission');

		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					['slug' => 'error']
				]
			]
		]);

		$app->impersonate('kirby');

		PageRules::delete($app->page('error'));
	}

	public function testDeleteWithChildren()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.page.delete.hasChildren');

		$page = new Page([
			'kirby' => $this->appWithAdmin(),
			'slug'  => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b']
			],
		]);

		PageRules::delete($page);
	}

	public function testDeleteWithChildrenForce()
	{
		$page = new Page([
			'kirby' => $this->appWithAdmin(),
			'slug'  => 'test',
			'children' => [
				['slug' => 'a'],
				['slug' => 'b']
			],
		]);

		$this->expectNotToPerformAssertions();

		PageRules::delete($page, true);
	}

	public function testDuplicate()
	{
		$page = new Page([
			'slug'  => 'test',
			'kirby' => $this->appWithAdmin(),
		]);

		$this->expectNotToPerformAssertions();

		PageRules::duplicate($page, 'test-copy');
	}

	public function testDuplicateInvalid()
	{
		$page = new Page([
			'slug'  => 'test',
			'kirby' => $this->appWithAdmin(),
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.invalid');

		PageRules::duplicate($page, '');
	}

	public function testDuplicateWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('duplicate')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to duplicate "test"');

		PageRules::duplicate($page, 'something');
	}

	public function testUpdate()
	{
		$page = new Page([
			'kirby' => $this->appWithAdmin(),
			'slug'  => 'test',
		]);

		$this->expectNotToPerformAssertions();

		PageRules::update($page, [
			'color' => 'red'
		]);
	}

	public function testUpdateWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('update')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update "test"');

		PageRules::update($page, []);
	}

	public function testValidateSlugMaxlength()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'user' => 'test@getkirby.com',
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			],
			'options' => [
				'slugs.maxlength' => 10
			]
		]);

		// valid
		$page = new Page([
			'slug'  => 'a-ten-slug',
			'kirby' => $app
		]);

		PageRules::create($page);

		$this->assertSame('a-ten-slug', $page->slug());
		$this->assertSame(10, strlen($page->slug()));

		// disabled with long slug that 273 characters
		// default slug maxlength is 255 characters
		$page = new Page([
			'slug' => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit-integer-metus-neque-molestie-ut-sagittis-eget-venenatis-quis-ipsum-ut-ultricies-hendrerit-magna-eu-molestie-enim-vestibulum-ante-ipsum-primis-in-faucibus-orci-luctus-et-ultrices-posuere-cubilia-curae-cras-nec-elementum',
			'kirby' => $app->clone([
				'options' => [
					'slugs.maxlength' => false
				]
			])
		]);

		PageRules::create($page);

		$this->assertSame(273, strlen($page->slug()));

		// invalid
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.slug.maxlength');

		$page = new Page([
			'slug'  => 'very-very-long-slug',
			'kirby' => $app->clone([
				'options' => [
					'slugs.maxlength' => 10
				]
			])
		]);

		PageRules::create($page);
	}

	public function testMove()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'template' => 'parent',
						'children' => [
							[
								'slug' => 'child',
								'template' => 'child'
							]
						]
					],
					[
						'slug'     => 'parent-b',
						'template' => 'parent',
					]
				]
			],
			'blueprints' => [
				'pages/parent' => [
					'sections' => [
						'subpages' => [
							'type'     => 'pages',
							'template' => 'child'
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$this->expectNotToPerformAssertions();

		$parentB = $app->page('parent-b');
		$child   = $app->page('parent-a/child');
		PageRules::move($child, $parentB);
	}

	public function testMoveWithoutPermissions()
	{
		$permissions = $this->createMock(PagePermissions::class);
		$permissions->method('can')->with('move')->willReturn(false);

		$page = $this->createMock(Page::class);
		$page->method('slug')->willReturn('test');
		$page->method('permissions')->willReturn($permissions);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to move "test"');

		PageRules::move($page, new Page(['slug' => 'test']));
	}

	public function testMoveWithDuplicate()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'children' => [
							[
								'slug' => 'child'
							]
						]
					],
					[
						'slug'     => 'parent-b',
						'children' => [
							[
								'slug' => 'child'
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$parentB = $app->page('parent-b');
		$child   = $app->page('parent-a/child');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A sub page with the URL appendix "child" already exists');

		PageRules::move($child, $parentB);
	}

	public function testMoveWithInvalidTemplate()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'template' => 'blog',
						'children' => [
							[
								'slug'     => 'child',
								'template' => 'article'
							]
						]
					],
					[
						'slug'     => 'parent-b',
						'template' => 'photography',
					]
				]
			],
			'blueprints' => [
				'pages/photography' => [
					'sections' => [
						'albums' => [
							'type'      => 'pages',
							'templates' => ['album']
						],
						'related' => [
							'type'      => 'pages',
							'parent'    => 'site.find("parent-a")',
							'create'    => 'album',
							'templates' => ['article']
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$parentB = $app->page('parent-b');
		$child   = $app->page('parent-a/child');

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The "article" template is not accepted as a subpage of "parent-b"');

		PageRules::move($child, $parentB);
	}

	public function testMoveWithParentWithNoPagesSections()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'template' => 'blog',
						'children' => [
							[
								'slug'     => 'child',
								'template' => 'article'
							]
						]
					],
					[
						'slug'     => 'parent-b',
						'template' => 'photography',
					]
				]
			],
			'blueprints' => [
				'pages/article' => [],
				'pages/photography' => [
					'sections' => [
						'albums' => [
							'type' => 'info',
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$parentB = $app->page('parent-b');
		$child   = $app->page('parent-a/child');

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The page "parent-b" cannot be a parent of any page because it lacks any pages sections in its blueprint');

		PageRules::move($child, $parentB);
	}

	public function testMoveWithParentWithNoTemplateRestrictions()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'parent-a',
						'template' => 'blog',
						'children' => [
							[
								'slug'     => 'child',
								'template' => 'article'
							]
						]
					],
					[
						'slug'     => 'parent-b',
						'template' => 'photography',
						'create'   => 'album'
					]
				]
			],
			'blueprints' => [
				'pages/article' => [],
				'pages/photography' => [
					'sections' => [
						'albums' => [
							'type' => 'pages'
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$parentB = $app->page('parent-b');
		$child   = $app->page('parent-a/child');

		$this->expectNotToPerformAssertions();

		PageRules::move($child, $parentB);
	}
}
