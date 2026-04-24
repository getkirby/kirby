<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Find::class)]
class FindTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.Find';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	public function testFileForPage(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'a.jpg']
						],
						'children' => [
							[
								'slug' => 'aa',
								'files' => [
									['filename' => 'aa.jpg']
								],
							]
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');
		$this->assertSame('a.jpg', Find::file('pages/a', 'a.jpg')->filename());
		$this->assertSame('aa.jpg', Find::file('pages/a+aa', 'aa.jpg')->filename());
	}

	public function testFileForSite(): void
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
				]
			]
		]);

		$app->impersonate('kirby');
		$this->assertSame('test.jpg', Find::file('site', 'test.jpg')->filename());
	}

	public function testFileForUser(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'files' => [
						['filename' => 'test.jpg']
					]
				]
			]
		]);

		$app->impersonate('kirby');
		$this->assertSame('test.jpg', Find::file('users/test@getkirby.com', 'test.jpg')->filename());
	}

	public function testFileNotFound(): void
	{
		$this->app->impersonate('kirby');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The file "nope.jpg" cannot be found');

		Find::file('site', 'nope.jpg');
	}

	public function testFileNotReadable(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'files/protected' => [
					'options' => ['read' => false]
				]
			],
			'site' => [
				'files' => [
					[
						'filename' => 'protected.jpg',
						'template' => 'protected'
					]
				]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$app->impersonate('admin@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The file "protected.jpg" cannot be found');

		Find::file('site', 'protected.jpg');
	}

	public function testLanguage(): void
	{
		$app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
					'default' => true
				],
				[
					'code'    => 'de',
					'name'    => 'Deutsch',
				]
			]
		]);

		$app->impersonate('kirby');

		$this->assertSame('en', Find::language('en')->code());
		$this->assertSame('de', Find::language('de')->code());
	}

	public function testLanguageNotFound(): void
	{
		$this->app->impersonate('kirby');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The language could not be found');

		Find::language('en');
	}

	public function testPage(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'children' => [
							[
								'slug' => 'aa'
							]
						],
					],
					[
						'slug' => 'b',
						'content' => ['uuid' => 'my-uuid']
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$a  = $app->page('a');
		$aa = $app->page('a/aa');
		$b  = $app->page('b');

		$this->assertSame($a, Find::page('a'));
		$this->assertSame($aa, Find::page('a/aa'));
		$this->assertSame($aa, Find::page('a+aa'));
		$this->assertSame($b, Find::page('page://my-uuid'));
	}

	public function testPageNotReadable(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'pages/protected' => [
					'options' => [
						'read' => false
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'a',
						'template' => 'protected'
					]
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "a" cannot be found');

		Find::page('a');
	}

	public function testPageNotFound(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "does-not-exist" cannot be found');

		Find::page('does-not-exist');
	}

	public function testRole(): void
	{
		$uuid = uuid();

		$app = $this->app->clone([
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$app->impersonate('admin@getkirby.com');
		$this->assertSame('editor-' . $uuid, Find::role('editor-' . $uuid)->name());
	}

	public function testRoleNotAccessibleForOwnRole(): void
	{
		$uuid = uuid();

		$app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor-' . $uuid,
					'permissions' => [
						'user' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The role "editor-' . $uuid . '" cannot be found');

		Find::role('editor-' . $uuid);
	}

	public function testRoleNotAccessibleForOtherRole(): void
	{
		$uuid = uuid();

		$app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'author-' . $uuid,
					'permissions' => [
						'users' => ['access' => false]
					]
				],
				[
					'name' => 'editor-' . $uuid
				]
			],
			'users' => [
				[
					'email' => 'author@getkirby.com',
					'role'  => 'author-' . $uuid
				]
			]
		]);

		$app->impersonate('author@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The role "editor-' . $uuid . '" cannot be found');

		Find::role('editor-' . $uuid);
	}

	public function testRoleNotFound(): void
	{
		$this->app->impersonate('kirby');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The role "does-not-exist" cannot be found');

		Find::role('does-not-exist');
	}

	public function testRoles(): void
	{
		$uuid = uuid();

		$app = $this->app->clone([
			'roles' => [
				['name' => 'editor-' . $uuid],
				['name' => 'author-' . $uuid]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$app->impersonate('admin@getkirby.com');
		$this->assertSame(3, $app->roles()->count()); // admin + editor + author
		$this->assertSame(3, Find::roles()->count());
	}

	public function testRolesNotAccessible(): void
	{
		$uuid = uuid();

		$app = $this->app->clone([
			'roles' => [
				[
					'name' => 'editor-' . $uuid,
					'permissions' => [
						'users' => [
							'access' => false
						],
					]
				],
				[
					'name' => 'author-' . $uuid,
					'permissions' => [
						'user' => [
							'access' => false
						],
						'users' => [
							'access' => false
						]
					]
				]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'author@getkirby.com',
					'role'  => 'author-' . $uuid
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			]
		]);

		// admin + editor both accessible to admin
		$app->impersonate('admin@getkirby.com');
		$this->assertSame(3, Find::roles()->count());

		// only their own role is accessible for the editor
		$app->impersonate('editor@getkirby.com');
		$this->assertSame(1, Find::roles()->count());
		$this->assertSame('editor-' . $uuid, Find::roles()->first()->name());

		// the author has no access to any role
		$app->impersonate('author@getkirby.com');
		$this->assertSame(0, Find::roles()->count());
	}

	public function testParent(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'children' => [
							[
								'slug' => 'aa'
							],
						],
						'files' => [
							['filename' => 'a-regular-file.jpg']
						]
					],
					[
						'slug' => 'files',
						'files' => [
							['filename' => 'file-in-files-page.jpg']
						]
					]
				],
				'files' => [
					['filename' => 'sitefile.jpg']
				]
			],
			'users' => [
				[
					'email' => 'current@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'test@getkirby.com',
					'files' => [
						['filename' => 'userfile.jpg']
					]
				]
			],
			'options' => [
				'api' => [
					'allowImpersonation' => true,
				]
			]
		]);

		$app->impersonate('current@getkirby.com');

		$this->assertIsUser(Find::parent('account'));
		$this->assertIsUser(Find::parent('users/test@getkirby.com'));
		$this->assertIsSite(Find::parent('site'));
		$this->assertIsSite(Find::parent('/site'));
		$this->assertIsPage(Find::parent('pages/a+aa'));
		$this->assertIsPage(Find::parent('pages/a aa'));
		$this->assertIsFile(Find::parent('site/files/sitefile.jpg'));
		$this->assertIsFile(Find::parent('pages/a/files/a-regular-file.jpg'));
		$this->assertIsFile(Find::parent('pages/files/files/file-in-files-page.jpg'));
		$this->assertIsFile(Find::parent('users/test@getkirby.com/files/userfile.jpg'));
	}

	public function testParentWithInvalidModelType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid model type: something');
		$this->assertNull(Find::parent('something/something'));
	}

	public function testParentNotFound(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "does-not-exist" cannot be found');
		$this->assertNull(Find::parent('pages/does-not-exist'));
	}

	public function testParentSiteNotAccessible(): void
	{
		$app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The site is not accessible');

		Find::parent('site');
	}

	public function testParentUndefined(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "does-not-exist" cannot be found');
		$this->assertNull(Find::parent('users/does-not-exist'));
	}

	public function testSite(): void
	{
		$this->app->impersonate('kirby');
		$this->assertIsSite(Find::site());
	}

	public function testSiteNotAccessible(): void
	{
		$app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'site' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The site is not accessible');

		Find::site();
	}

	public function testParentUserNotAccessible(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name'    => 'admin',
					'options' => ['access' => false]
				]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "admin@getkirby.com" cannot be found');

		Find::parent('users/admin@getkirby.com');
	}

	public function testUser(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$app->impersonate('kirby');
		$this->assertSame('test@getkirby.com', Find::user('test@getkirby.com')->email());
	}

	public function testUserWithAuthentication(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			],
			'options' => [
				'api' => [
					'allowImpersonation' => true,
				]
			]
		]);

		$app->impersonate('test@getkirby.com');
		$this->assertSame('test@getkirby.com', Find::user()->email());
	}

	public function testUserWithAuthenticationNotAccessible(): void
	{
		$app = $this->app->clone([
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'user' => ['access' => false]
					]
				]
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			],
			'options' => [
				'api' => [
					'allowImpersonation' => true,
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user cannot be found');

		Find::user();
	}

	public function testUserWithoutAllowedImpersonation(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user cannot be found');

		Find::user()->email();
	}

	public function testUserForAccountArea(): void
	{
		$app = $this->app->clone([
			'options' => [
				'api' => [
					'allowImpersonation' => true
				]
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$app->impersonate('test@getkirby.com');
		$this->assertSame('test@getkirby.com', Find::user('account')->email());
	}

	public function testUserNotAccessible(): void
	{
		$app = $this->app->clone([
			'blueprints' => [
				'users/admin' => [
					'name'    => 'admin',
					'options' => ['access' => false]
				]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin'
				],
				[
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->impersonate('test@getkirby.com');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "admin@getkirby.com" cannot be found');

		Find::user('admin@getkirby.com');
	}

	public function testUserNotFound(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "nope@getkirby.com" cannot be found');

		Find::user('nope@getkirby.com');
	}

	public function testUsers(): void
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'admin@getkirby.com',
				],
				[
					'email' => 'editor@getkirby.com',
				]
			]
		]);

		$app->impersonate('kirby');

		$this->assertSame($app->users()->pluck('email'), Find::users()->pluck('email'));
	}

	public function testUsersNotAccessible(): void
	{
		// create a random role id to avoid permission cache issues
		$uuid = uuid();

		$app = $this->app->clone([
			'blueprints' => [
				'users/admin-' . $uuid => [
					'name' => 'admin-' . $uuid,
				],
				'users/editor-' . $uuid => [
					'name' => 'editor-' . $uuid,
					'permissions' => [
						'users' => [
							'access' => false
						]
					]
				],
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'role'  => 'admin-' . $uuid
				],
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				],
			]
		]);

		$app->impersonate('admin@getkirby.com');

		$this->assertSame(2, $app->users()->count());
		$this->assertSame(2, Find::users()->count());


		$app->impersonate('editor@getkirby.com');
		$this->assertSame(2, $app->users()->count());
		$this->assertSame(1, Find::users()->count());
		$this->assertSame('editor-' . $uuid, Find::users()->first()->role()->name());
	}
}
