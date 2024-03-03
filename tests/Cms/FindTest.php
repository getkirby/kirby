<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;

/**
 * @coversDefaultClass \Kirby\Cms\Find
 */
class FindTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Find';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	/**
	 * @covers ::file
	 */
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

	/**
	 * @covers ::file
	 */
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

	/**
	 * @covers ::file
	 */
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

	/**
	 * @covers ::file
	 */
	public function testFileNotFound()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The file "nope.jpg" cannot be found');

		Find::file('site', 'nope.jpg');
	}

	/**
	 * @covers ::file
	 */
	public function testFileNotReadable()
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'protected.jpg',
						'template' => 'protected'
					]
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The file "protected.jpg" cannot be found');

		Find::file('site', 'protected.jpg');
	}

	/**
	 * @covers ::language
	 */
	public function testLanguage()
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

	/**
	 * @covers ::language
	 */
	public function testLanguageNotFound()
	{
		$this->app->impersonate('kirby');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The language could not be found');

		Find::language('en');
	}

	/**
	 * @covers ::page
	 */
	public function testPage()
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

	/**
	 * @covers ::page
	 */
	public function testPageNotReadable()
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

	/**
	 * @covers ::page
	 */
	public function testPageNotFound()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "does-not-exist" cannot be found');

		Find::page('does-not-exist');
	}

	/**
	 * @covers ::parent
	 */
	public function testParent()
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

	/**
	 * @covers ::parent
	 */
	public function testParentWithInvalidModelType()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid model type: something');
		$this->assertNull(Find::parent('something/something'));
	}

	/**
	 * @covers ::parent
	 */
	public function testParentNotFound()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The page "does-not-exist" cannot be found');
		$this->assertNull(Find::parent('pages/does-not-exist'));
	}

	/**
	 * @covers ::parent
	 */
	public function testParentUndefined()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user cannot be found');
		$this->assertNull(Find::parent('users/does-not-exist'));
	}

	/**
	 * @covers ::user
	 */
	public function testUser()
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

	/**
	 * @covers ::user
	 */
	public function testUserWithAuthentication()
	{
		$app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
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

	/**
	 * @covers ::user
	 */
	public function testUserWithoutAllowedImpersonation()
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

	/**
	 * @covers ::user
	 */
	public function testUserForAccountArea()
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

	/**
	 * @covers ::user
	 */
	public function testUserNotFound()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The user "nope@getkirby.com" cannot be found');

		Find::user('nope@getkirby.com');
	}
}
