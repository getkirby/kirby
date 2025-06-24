<?php

namespace Kirby\Cms;

use Kirby\Cms\System\UpdateStatus;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * @coversDefaultClass \Kirby\Cms\System
 */
class SystemTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/SystemTest';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.System';

	protected static string $updateStatusHost;
	protected string|null $subTmp = null;

	public static function setUpBeforeClass(): void
	{
		static::$updateStatusHost = UpdateStatus::$host;
		UpdateStatus::$host = 'file://' . static::FIXTURES;
	}

	public static function tearDownAfterClass(): void
	{
		UpdateStatus::$host = static::$updateStatusHost;
	}

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	public function tearDown(): void
	{
		if ($this->subTmp !== null) {
			chmod($this->subTmp, 0o755);
			Dir::remove($this->subTmp);
		}

		parent::tearDown();
	}

	public static function providerForIndexUrls(): array
	{
		return [
			['http://getkirby.com', 'getkirby.com'],
			['https://getkirby.com', 'getkirby.com'],
			['https://getkirby.com/test', 'getkirby.com/test'],
			['/', '/'],
			['/test', '/test']
		];
	}

	public static function providerForLoginMethods(): array
	{
		return [
			[
				'password',
				['password' => []]
			],
			[
				'password-reset',
				['password-reset' => []]
			],
			[
				['password-reset'],
				['password-reset' => []]
			],
			[
				['password-reset' => true],
				['password-reset' => []]
			],
			[
				['password-reset' => []],
				['password-reset' => []]
			],
			[
				['password-reset' => ['option' => 'test']],
				['password-reset' => ['option' => 'test']]
			],
			[
				['password', 'password-reset'],
				['password' => [], 'password-reset' => []]
			],
			[
				['code', 'password'],
				['code' => [], 'password' => []]
			],
			[
				['code', 'password-reset'],
				['password-reset' => []]
			],
			[
				['password' => ['2fa' => true], 'code'],
				['password' => ['2fa' => true]]
			],
			[
				['password' => ['2fa' => true], 'password-reset'],
				['password' => ['2fa' => true]]
			],
			[
				['password' => ['2fa' => true], 'code', 'password-reset'],
				['password' => ['2fa' => true]]
			]
		];
	}

	public static function providerForRoots(): array
	{
		return [
			['accounts'],
			['content'],
			['media'],
			['sessions'],
		];
	}

	public static function providerForServerNames(): array
	{
		return [
			['localhost', true],
			['mydomain.local', true],
			['mydomain.test', true],
			['mydomain.com', false],
			['mydomain.dev', false],
		];
	}

	/**
	 * @covers ::exposedFileUrl
	 * @covers ::folderUrl
	 */
	public function testFolderUrlForContentFolder()
	{
		$system = new System($this->app->clone([
			'roots' => [
				'content' => static::TMP . '/content',
				'index'   => static::TMP
			]
		]));

		Dir::remove(static::TMP . '/content');

		$this->assertNull($system->folderUrl('content'));
		$this->assertNull($system->exposedFileUrl('content'));

		Dir::make(static::TMP . '/content');

		$this->assertSame('/content', $system->folderUrl('content'));
		$this->assertSame('/content/site.txt', $system->exposedFileUrl('content'));
	}

	/**
	 * @covers ::exposedFileUrl
	 * @covers ::folderUrl
	 */
	public function testFolderUrlForGitFolder()
	{
		$system = new System($this->app->clone([
			'roots' => [
				'index' => static::TMP
			]
		]));

		Dir::remove(static::TMP . '/.git');

		$this->assertNull($system->folderUrl('git'));
		$this->assertNull($system->exposedFileUrl('git'));

		Dir::make(static::TMP . '/.git');

		$this->assertSame('/.git', $system->folderUrl('git'));
		$this->assertSame('/.git/config', $system->exposedFileUrl('git'));
	}

	/**
	 * @covers ::exposedFileUrl
	 * @covers ::folderUrl
	 */
	public function testFolderUrlForInsignificantFolder()
	{
		$system = new System($this->app->clone([
			'roots' => [
				'index' => static::TMP,
				'media' => static::TMP . '/media'
			]
		]));

		Dir::make(static::TMP . '/media');

		$this->assertSame('/media', $system->folderUrl('media'));
		$this->assertNull($system->exposedFileUrl('media'));
	}

	/**
	 * @covers ::exposedFileUrl
	 * @covers ::folderUrl
	 */
	public function testFolderUrlForKirbyFolder()
	{
		$system = new System($this->app->clone([
			'roots' => [
				'kirby' => static::TMP . '/kirby',
				'index' => static::TMP
			]
		]));

		Dir::remove(static::TMP . '/kirby');

		$this->assertNull($system->folderUrl('kirby'));
		$this->assertNull($system->exposedFileUrl('kirby'));

		Dir::make(static::TMP . '/kirby');

		$this->assertSame('/kirby', $system->folderUrl('kirby'));
		$this->assertSame('/kirby/LICENSE.md', $system->exposedFileUrl('kirby'));
	}

	/**
	 * @covers ::exposedFileUrl
	 * @covers ::folderUrl
	 */
	public function testFolderUrlForSiteFolder()
	{
		$system = new System($this->app->clone([
			'roots' => [
				'site'  => static::TMP . '/site',
				'index' => static::TMP
			]
		]));

		Dir::remove(static::TMP . '/site');

		$this->assertNull($system->folderUrl('site'));

		// with blueprints
		Dir::remove(static::TMP . '/site');
		F::write(static::TMP . '/site/blueprints/site.yml', 'test');

		$this->assertSame('/site', $system->folderUrl('site'));
		$this->assertSame('/site/blueprints/site.yml', $system->exposedFileUrl('site'));

		// with templates
		Dir::remove(static::TMP . '/site');
		F::write(static::TMP . '/site/templates/default.php', 'test');

		$this->assertSame('/site', $system->folderUrl('site'));
		$this->assertSame('/site/templates/default.php', $system->exposedFileUrl('site'));

		// with snippets
		Dir::remove(static::TMP . '/site');
		F::write(static::TMP . '/site/snippets/header.php', 'test');

		$this->assertSame('/site', $system->folderUrl('site'));
		$this->assertSame('/site/snippets/header.php', $system->exposedFileUrl('site'));
	}

	/**
	 * @covers ::exposedFileUrl
	 * @covers ::folderUrl
	 */
	public function testFolderUrlForUnknownFolder()
	{
		$system = new System($this->app->clone([
			'roots' => [
				'index' => static::TMP
			]
		]));

		$this->assertNull($system->folderUrl('unknown'));
		$this->assertNull($system->exposedFileUrl('unknown'));
	}

	/**
	 * @covers ::indexUrl
	 * @dataProvider providerForIndexUrls
	 */
	public function testIndexUrl($indexUrl, $expected)
	{
		$system = new System($this->app->clone([
			'options' => [
				'url' => $indexUrl
			]
		]));
		$this->assertSame($expected, $system->indexUrl($indexUrl));
	}

	/**
	 * @dataProvider providerForRoots
	 * @throws \Kirby\Exception\PermissionException
	 */
	public function testInitPermission($root)
	{
		$this->subTmp = static::TMP . '/' . ucfirst($root) . 'Test';

		$app = $this->app->clone([
			'roots' => [
				'index' => static::TMP,
				$root   => $this->subTmp . '/' . $root,
			]
		]);

		// create test roots
		Dir::make($this->subTmp);

		// set no writable
		chmod($this->subTmp, 0o444);

		// /site/accounts
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The ' . $root . ' directory could not be created');

		new System($app);
	}

	/**
	 * @covers ::info
	 */
	public function testInfo()
	{
		$app = $this->app->clone([
			'languages' => [
				'en' => [
					'code' => 'en'
				],
				'de' => [
					'code' => 'de'
				]
			],
		]);
		$system = new System($app);
		$info   = $system->info();
		$this->assertSame(['en', 'de'], $info['languages']);
	}

	/**
	 * @covers ::is2FA
	 */
	public function testIs2FA()
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password']
				]
			]
		]);
		$system = new System($app);
		$this->assertFalse($system->is2FA());

		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password' => ['2fa' => true]]
				]
			]
		]);
		$system = new System($app);
		$this->assertTrue($system->is2FA());
	}

	/**
	 * @covers ::is2FAwithTOTP
	 */
	public function testIs2FAWithTOTP()
	{
		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password']
				]
			]
		]);
		$system = new System($app);
		$this->assertFalse($system->is2FAWithTOTP());

		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'methods' => ['password' => ['2fa' => true]]
				]
			]
		]);
		$system = new System($app);
		$this->assertTrue($system->is2FAWithTOTP());

		$app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['email'],
					'methods' => ['password' => ['2fa' => true]]
				]
			]
		]);
		$system = new System($app);
		$this->assertFalse($system->is2FAWithTOTP());
	}

	/**
	 * @covers ::isInstallable
	 */
	public function testIsInstallableOnLocalhost()
	{
		$app = $this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$system = new System($app);

		$this->assertTrue($system->isInstallable());
	}

	/**
	 * @covers ::isInstallable
	 */
	public function testIsInstallableOnPublicServer()
	{
		$app = $this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '1.2.3.4',
			]
		]);

		$system = new System($app);

		$this->assertFalse($system->isInstallable());
	}

	/**
	 * @covers ::isInstallable
	 */
	public function testIsInstallableOnPublicServerWithOverride()
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'install' => true
				]
			],
			'server' => [
				'REMOTE_ADDR' => '1.2.3.4',
			]
		]);

		$system = new System($app);

		$this->assertTrue($system->isInstallable());
	}

	/**
	 * @covers ::isInstalled
	 */
	public function testIsInstalled()
	{
		$system = new System($this->app);
		$this->assertFalse($system->isInstalled());

		$this->app->users()->create([
			'email'    => 'test@getkirby.com',
			'password' => 'test123456'
		]);

		$this->assertTrue($system->isInstalled());
	}

	/**
	 * @covers ::isLocal
	 */
	public function testIsLocal()
	{
		// yep
		$app = $this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$system = new System($app);

		$this->assertTrue($system->isLocal());

		// nope
		$app = $this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '1.2.3.4',
			]
		]);

		$system = new System($app);

		$this->assertFalse($system->isLocal());
	}

	/**
	 * @covers ::isOk
	 */
	public function testIsOk()
	{
		$app = $this->app->clone([
			'server' => [
				'REMOTE_ADDR'     => '127.0.0.1',
				'SERVER_SOFTWARE' => 'Apache'
			]
		]);

		$system = new System($app);

		$this->assertTrue($system->isOk());
	}

	/**
	 * @covers ::isOk
	 */
	public function testIsOkContentMissingPermissions()
	{
		// reset permissions in `tearDown()`
		$this->subTmp = static::TMP . '/content';

		$system = new System($this->app);

		chmod($this->app->root('content'), 0o000);

		$this->assertFalse($system->isOk());
	}

	/**
	 * @covers ::license
	 */
	public function testLicense()
	{
		$system = new System($this->app);
		$this->assertInstanceOf(License::class, $system->license());
	}

	/**
	 * @covers ::loginMethods
	 */
	public function testLoginMethods()
	{
		$this->assertSame(['password' => []], $this->app->system()->loginMethods());
	}

	/**
	 * @covers ::loginMethods
	 * @dataProvider providerForLoginMethods
	 */
	public function testLoginMethodsCustom($option, $expected)
	{
		$app = $this->app->clone([
			'options' => [
				'auth.methods' => $option
			]
		]);
		$this->assertSame($expected, $app->system()->loginMethods());
	}

	/**
	 * @covers ::loginMethods
	 */
	public function testLoginMethodsDebug1()
	{
		$app = $this->app->clone([
			'options' => [
				'debug' => true,
				'auth.methods' => ['code', 'password-reset']
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" and "password-reset" login methods cannot be enabled together');
		$app->system()->loginMethods();
	}

	/**
	 * @covers ::loginMethods
	 */
	public function testLoginMethodsDebug2()
	{
		$app = $this->app->clone([
			'options' => [
				'debug' => true,
				'auth.methods' => [
					'password' => ['2fa' => true],
					'code'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "code" login method cannot be enabled when 2FA is required');
		$app->system()->loginMethods();
	}

	/**
	 * @covers ::loginMethods
	 */
	public function testLoginMethodsDebug3()
	{
		$app = $this->app->clone([
			'options' => [
				'debug' => true,
				'auth.methods' => [
					'password' => ['2fa' => true],
					'password-reset'
				]
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "password-reset" login method cannot be enabled when 2FA is required');
		$app->system()->loginMethods();
	}

	/**
	 * @covers ::plugins
	 */
	public function testPlugins()
	{
		$system = new System($this->app);
		$this->assertInstanceOf(Collection::class, $system->plugins());
	}

	/**
	 * @covers ::serverSoftware
	 */
	public function testServerSoftware()
	{
		$app = $this->app->clone([
			'server' => [
				'SERVER_SOFTWARE' => $software = 'Apache'
			]
		]);

		$system = new System($app);
		$this->assertSame($software, $system->serverSoftware());
	}

	/**
	 * @covers ::serverSoftware
	 */
	public function testServerSoftwareInvalid()
	{
		$app = $this->app->clone([
			'server' => [
				'SERVER_SOFTWARE' => null
			]
		]);

		$system = new System($app);
		$this->assertSame('–', $system->serverSoftware());
	}

	/**
	 * @covers ::serverSoftwareShort
	 */
	public function testServerSoftwareShort()
	{
		$app = $this->app->clone([
			'server' => [
				'SERVER_SOFTWARE' => $software = 'nginx/1.25.4'
			]
		]);

		$system = new System($app);
		$this->assertSame($software, $system->serverSoftwareShort());

		$app = $this->app->clone([
			'server' => [
				'SERVER_SOFTWARE' => $software = 'Apache/2.4.7 (Ubuntu)'
			]
		]);

		$system = new System($app);
		$this->assertSame('Apache/2.4.7', $system->serverSoftwareShort());
	}

	/**
	 * @covers ::accounts
	 * @covers ::content
	 * @covers ::curl
	 * @covers ::sessions
	 * @covers ::mbstring
	 * @covers ::media
	 * @covers ::php
	 * @covers ::status
	 * @covers ::toArray
	 * @covers ::__debugInfo
	 */
	public function testStatus()
	{
		$system = new System($this->app);

		$expected = [
			'accounts' => true,
			'content'  => true,
			'curl'     => true,
			'sessions' => true,
			'mbstring' => true,
			'media'    => true,
			'php'      => true
		];
		$this->assertSame($expected, $system->status());
		$this->assertSame($expected, $system->toArray());
		$this->assertSame($expected, $system->__debugInfo());
	}

	/**
	 * @covers ::content
	 * @covers ::status
	 */
	public function testStatusContentMissingPermissions()
	{
		// reset permissions in `tearDown()`
		$this->subTmp = static::TMP . '/content';

		$system = new System($this->app);

		chmod($this->app->root('content'), 0o000);

		$expected = [
			'accounts' => true,
			'content'  => false,
			'curl'     => true,
			'sessions' => true,
			'mbstring' => true,
			'media'    => true,
			'php'      => true
		];
		$this->assertSame($expected, $system->status());
		$this->assertSame($expected, $system->toArray());
		$this->assertSame($expected, $system->__debugInfo());
	}

	/**
	 * @covers ::title
	 */
	public function testTitle()
	{
		$app = $this->app->clone([
			'blueprints' => [
				'site' => [
					'title' => $expected = 'Great site'
				]
			]
		]);

		$this->assertSame($expected, $app->system()->title());

		$app = $app->clone([
			'site' => [
				'content' => [
					'title' => $expected = 'Better site'
				]
			]
		]);

		$this->assertSame($expected, $app->system()->title());
	}

	/**
	 * @covers ::updateStatus
	 */
	public function testUpdateStatus()
	{
		$system       = new System($this->app);
		$updateStatus = $system->updateStatus();

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $system->updateStatus());

		// should use the requested data and
		// suggest feature updates by default
		$this->assertSame('update', $updateStatus->status());
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
	}

	/**
	 * @covers ::updateStatus
	 */
	public function testUpdateStatusDisabled1()
	{
		$app = $this->app->clone([
			'options' => [
				'updates' => [
					'kirby' => false
				]
			]
		]);

		$system       = new System($app);
		$updateStatus = $system->updateStatus();

		$this->assertNull($updateStatus);
	}

	/**
	 * @covers ::updateStatus
	 */
	public function testUpdateStatusDisabled2()
	{
		$app = $this->app->clone([
			'options' => [
				'updates' => false
			]
		]);

		$system       = new System($app);
		$updateStatus = $system->updateStatus();

		$this->assertNull($updateStatus);
	}

	/**
	 * @covers ::updateStatus
	 */
	public function testUpdateStatusSecurity1()
	{
		$app = $this->app->clone([
			'options' => [
				'updates' => [
					'kirby' => 'security'
				]
			]
		]);

		$system       = new System($app);
		$updateStatus = $system->updateStatus();

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $system->updateStatus());

		// should use the requested data and
		// only suggest security updates
		$this->assertSame('not-vulnerable', $updateStatus->status());
		$this->assertNull($updateStatus->targetVersion());
	}

	/**
	 * @covers ::updateStatus
	 */
	public function testUpdateStatusSecurity2()
	{
		$app = $this->app->clone([
			'options' => [
				'updates' => 'security'
			]
		]);

		$system       = new System($app);
		$updateStatus = $system->updateStatus();

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $system->updateStatus());

		// should use the requested data and
		// only suggest security updates
		$this->assertSame('not-vulnerable', $updateStatus->status());
		$this->assertNull($updateStatus->targetVersion());
	}

	/**
	 * @covers ::updateStatus
	 */
	public function testUpdateStatusCustomData()
	{
		$system       = new System($this->app);
		$updateStatus = $system->updateStatus([
			'latest' => '87654.3.2',
			'versions' => [
				'*' => [
					'latest' => $this->app->version(),
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://other-domain.com/releases/{{ version }}',
					'upgrade' => 'https://other-domain.com/releases/87654'
				]
			],
			'incidents' => [],
			'messages' => []
		]);

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $system->updateStatus());

		// should use the passed data
		$this->assertSame('upgrade', $updateStatus->status());
		$this->assertSame('87654.3.2', $updateStatus->targetVersion());
		$this->assertSame('https://other-domain.com/releases/87654', $updateStatus->url());
	}
}
