<?php

namespace Kirby\Plugin;

use Composer\Autoload\ClassLoader;
use Kirby\Cms\App;
use Kirby\Cms\System\UpdateStatus;
use Kirby\Cms\TestCase;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Plugin::class)]
class PluginTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Plugin';

	protected static ClassLoader $classLoader;
	protected static string $updateStatusHost;

	public static function setUpBeforeClass(): void
	{
		static::$updateStatusHost = UpdateStatus::$host;
		UpdateStatus::$host = 'file://' . static::FIXTURES . '/updateStatus';

		static::$classLoader = new ClassLoader(static::FIXTURES . '/vendor');
		static::$classLoader->register();
	}

	public static function tearDownAfterClass(): void
	{
		UpdateStatus::$host = static::$updateStatusHost;

		static::$classLoader->unregister();
	}

	public function setUp(): void
	{
		App::destroy();

		Dir::make(static::TMP);

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);
	}

	public function tearDown(): void
	{
		App::destroy();
		Dir::remove(static::TMP);
	}

	public function test__call()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin'
		);

		$this->assertSame('https://getkirby.com', $plugin->homepage());
	}

	public function testAsset()
	{
		$root   = static::FIXTURES . '/plugin-assets';
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			extends: [
				'assets' => [
					'c.css' => $a = $root . '/a.css',
					'd.css' => $b = $root . '/foo/b.css'
				]
			]
		);

		$this->assertSame($a, $plugin->asset('c.css')->root());
		$this->assertSame($b, $plugin->asset('d.css')->root());
	}

	public function testAssets()
	{
		$root = static::FIXTURES . '/plugin-assets';

		// assets defined in plugin config
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: $root,
			extends: [
				'assets' => [
					'c.css' => $root . '/a.css',
				]
			]
		);

		$this->assertInstanceOf(Assets::class, $plugin->assets());
		$this->assertSame($root . '/a.css', $plugin->asset('c.css')->root());
	}

	public function testAuthors()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root:  static::FIXTURES . '/plugin'
		);

		$authors = [
			[
				'name'  => 'A',
				'email' => 'a@getkirby.com'
			],
			[
				'name'  => 'B',
				'email' => 'b@getkirby.com'
			]
		];

		$this->assertSame($authors, $plugin->authors());
	}

	public function testAuthorsNames()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin'
		);

		$this->assertSame('A, B', $plugin->authorsNames());
	}

	public function testExtends()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			extends: $extends = [
				'fields' => [
					'test' => []
				]
			]
		);

		$this->assertSame($extends, $plugin->extends());
	}

	public function testId()
	{
		$plugin = new Plugin($id = 'abc-1234/DEF-56789');
		$this->assertSame($id, $plugin->id());
	}

	public function testInfo()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin-version'
		);

		$authors = [
			[
				'name'  => 'A',
				'email' => 'a@getkirby.com'
			],
			[
				'name'  => 'B',
				'email' => 'b@getkirby.com'
			]
		];

		$this->assertSame('getkirby/test-plugin', $plugin->info()['name']);
		$this->assertSame('MIT', $plugin->info()['license']);
		$this->assertSame('1.0.0', $plugin->info()['version']);
		$this->assertSame('plugin', $plugin->info()['type']);
		$this->assertSame('Some really nice description', $plugin->info()['description']);
		$this->assertSame($authors, $plugin->info()['authors']);
	}

	public function testInfoFromProps()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			info: [
				'license' => 'MIT',
				'authors' => [
					[
						'name'  => 'A',
						'email' => 'a@getkirby.com'
					],
					[
						'name'  => 'B',
						'email' => 'b@getkirby.com'
					]
				]
			]
		);

		$this->assertSame('MIT', $plugin->info()['license']);
		$this->assertSame('A, B', $plugin->authorsNames());
	}

	public function testInfoFromPropAndManifest()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin-version',
			info: [
				'version' => '2.1.0'
			],
			version: '5.3.0'
		);

		$this->assertSame('MIT', $plugin->info()['license']);
		$this->assertSame('2.1.0', $plugin->info()['version']);
		$this->assertSame('5.3.0', $plugin->version());
	}

	public function testInfoWhenEmpty()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: __DIR__
		);

		$this->assertSame([], $plugin->info());
	}

	public function testLicense(): void
	{
		$plugin = new Plugin(name: 'getkirby/test-plugin');
		$this->assertInstanceOf(License::class, $license = $plugin->license());
	}

	public function testLicenseFromString(): void
	{
		$plugin = new Plugin(name: 'getkirby/test-plugin', license: 'mit');
		$this->assertInstanceOf(License::class, $plugin->license());
		$this->assertSame('mit', $plugin->license()->name());
	}

	public function testLinkFromHomepage()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			info: [
				'homepage' => 'https://getkirby.com'
			]
		);

		$this->assertSame('https://getkirby.com', $plugin->link());
	}

	public function testLinkFromInvalidHomepage()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			info: [
				'homepage' => 'test'
			]
		);

		$this->assertNull($plugin->link());
	}

	public function testLinkFromSupportDocs()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			info: [
				'support' => [
					'docs' => 'https://getkirby.com'
				]
			]
		);

		$this->assertSame('https://getkirby.com', $plugin->link());
	}

	public function testLinkFromSupportSource()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			info: [
				'support' => [
					'source' => 'https://getkirby.com'
				]
			]
		);

		$this->assertSame('https://getkirby.com', $plugin->link());
	}

	public function testLinkWhenEmpty()
	{
		$plugin = new Plugin('getkirby/test-plugin');
		$this->assertNull($plugin->link());
	}

	public function testMediaRoot()
	{
		$this->app->clone([
			'roots' => [
				'media' => $media = __DIR__ . '/media'
			]
		]);

		$plugin = new Plugin('getkirby/test-plugin');

		$this->assertSame($media . '/plugins/getkirby/test-plugin', $plugin->mediaRoot());
	}

	public function testMediaUrl()
	{
		$this->app->clone([
			'urls' => [
				'index' => '/'
			]
		]);

		$plugin = new Plugin('getkirby/test-plugin');

		$this->assertSame('/media/plugins/getkirby/test-plugin', $plugin->mediaUrl());
	}

	public function testManifest()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: __DIR__
		);

		$this->assertSame(__DIR__ . '/composer.json', $plugin->manifest());
	}

	public function testName()
	{
		$plugin = new Plugin($name = 'abc-1234/DEF-56789');
		$this->assertSame($name, $plugin->name());
	}

	public function testNameWithInvalidInput()
	{
		$this->expectException(InvalidArgumentException::class);
		new Plugin('äöü/!!!');
	}

	public function testOption()
	{
		App::plugin(
			name: 'developer/plugin',
			extends: [
				'options' => [
					'foo' => 'bar'
				]
			]
		);

		$app = $this->app->clone();

		$this->assertSame('bar', $app->plugin('developer/plugin')->option('foo'));
		$this->assertSame('bar', $app->option('developer.plugin.foo'));
	}

	public function testPrefix()
	{
		$plugin = new Plugin('getkirby/test-plugin');
		$this->assertSame('getkirby.test-plugin', $plugin->prefix());
	}

	public function testRoot()
	{
		$plugin = new Plugin('getkirby/test-plugin');
		$this->assertSame(__DIR__, $plugin->root());
	}

	public function testRootWithCustomSetup()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: $custom = __DIR__ . '/test'
		);

		$this->assertSame($custom, $plugin->root());
	}

	public function testToArray()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: $root = static::FIXTURES . '/plugin-version'
		);

		$expected = [
			'authors' => [
				['name' => 'A', 'email' => 'a@getkirby.com'],
				['name' => 'B', 'email' => 'b@getkirby.com']
			],
			'description' => 'Some really nice description',
			'name'        => 'getkirby/test-plugin',
			'license'     => [
				'link'   => null,
				'name'   => 'MIT',
				'status' => [
					'dialog' => null,
					'drawer' => null,
					'icon'   => 'check',
					'label'  => 'Valid license',
					'link'   => null,
					'theme'  => 'positive',
					'value'  => 'active',
				]
			],
			'link'        => 'https://getkirby.com',
			'root'        => $root,
			'version'     => '1.0.0'
		];

		$this->assertSame($expected, $plugin->toArray());
	}

	public function testUpdateStatus()
	{
		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin-version'
		);
		$updateStatus = $plugin->updateStatus();

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $plugin->updateStatus());

		// should use the requested data and
		// suggest feature updates by default
		$this->assertSame('update', $updateStatus->status());
		$this->assertSame('1.0.0', $updateStatus->currentVersion());
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
	}

	public function testUpdateStatusWithPrefix()
	{
		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin-version-prefix'
		);
		$updateStatus = $plugin->updateStatus();

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $plugin->updateStatus());

		// should use the requested data and
		// suggest feature updates by default
		$this->assertSame('update', $updateStatus->status());
		$this->assertSame('1.0.0', $updateStatus->currentVersion());
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
	}

	public function testUpdateStatusWithoutVersion()
	{
		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin'
		);

		$updateStatus = $plugin->updateStatus();

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $plugin->updateStatus());

		// should use the requested data;
		// error (because no current version is known)
		$this->assertSame('error', $updateStatus->status());
		$this->assertNull($updateStatus->currentVersion());
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());
	}

	public function testUpdateStatusUnknownPlugin()
	{
		$plugin = new Plugin(
			name: 'getkirby/unknown',
			root: static::FIXTURES . '/plugin-version'
		);

		$updateStatus = $plugin->updateStatus();

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $plugin->updateStatus());

		// should use the requested data;
		// error (because getkirby.com provides no data)
		$this->assertSame('error', $updateStatus->status());
		$this->assertSame('1.0.0', $updateStatus->currentVersion());
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin getkirby/unknown: Couldn\'t open file ' .
			static::FIXTURES . '/updateStatus/plugins/getkirby/unknown.json'
		], $updateStatus->exceptionMessages());
	}

	public function testUpdateStatusDisabled1()
	{
		$this->app->clone([
			'options' => [
				'updates' => [
					'plugins' => false
				]
			]
		]);

		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin-version'
		);
		$updateStatus = $plugin->updateStatus();

		$this->assertNull($updateStatus);
	}

	public function testUpdateStatusDisabled2()
	{
		$this->app->clone([
			'options' => [
				'updates' => [
					'plugins' => [
						'*' => true,
						'getkirby/*' => true,
						'getkirby/something' => true,
						'getkirby/pub*' => false
					]
				]
			]
		]);

		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin-version'
		);

		$updateStatus = $plugin->updateStatus();

		$this->assertNull($updateStatus);
	}

	public function testUpdateStatusDisabled3()
	{
		$this->app->clone([
			'options' => [
				'updates' => false
			]
		]);

		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin-version'
		);

		$updateStatus = $plugin->updateStatus();

		$this->assertNull($updateStatus);
	}

	public function testUpdateStatusDisabled4()
	{
		// the plugin update check does not support the
		// security mode yet because the hub is missing
		// where plugin devs can manage the security status
		// of their plugins
		$this->app->clone([
			'options' => [
				'updates' => 'security'
			]
		]);

		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin-version'
		);

		$updateStatus = $plugin->updateStatus();

		$this->assertNull($updateStatus);
	}

	public function testUpdateStatusNoCustomConfig()
	{
		$this->app->clone([
			'options' => [
				'updates' => [
					'plugins' => [
						'getkirby/something' => false
					]
				]
			]
		]);

		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin-version'
		);
		$updateStatus = $plugin->updateStatus();

		$this->assertInstanceOf(UpdateStatus::class, $updateStatus);

		// instance should be cached
		$this->assertSame($updateStatus, $plugin->updateStatus());

		// should use the requested data and
		// suggest feature updates by default
		$this->assertSame('update', $updateStatus->status());
		$this->assertSame('1.0.0', $updateStatus->currentVersion());
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
	}

	public function testUpdateStatusCustomData()
	{
		$plugin = new Plugin(
			name: 'getkirby/public',
			root: static::FIXTURES . '/plugin-version'
		);

		$updateStatus = $plugin->updateStatus([
			'latest' => '87654.3.2',
			'versions' => [
				'*' => [
					'latest' => $plugin->version(),
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
		$this->assertSame($updateStatus, $plugin->updateStatus());

		// should use the passed data
		$this->assertSame('upgrade', $updateStatus->status());
		$this->assertSame('87654.3.2', $updateStatus->targetVersion());
		$this->assertSame('https://other-domain.com/releases/87654', $updateStatus->url());
	}

	public function testVersion()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin-version'
		);

		$this->assertSame('1.0.0', $plugin->version());
	}

	public function testVersionFromArgument()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin-version',
			version: '1.2.0'
		);

		$this->assertSame('1.2.0', $plugin->version());
	}

	public function testVersionFromInfo()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin-version',
			info: [
				'version' => '1.1.0'
			]
		);

		$this->assertSame('1.1.0', $plugin->version());
	}

	public function testVersionMissing()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin'
		);

		$this->assertNull($plugin->version());
	}

	public function testVersionPrefixed()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin-version-prefix'
		);

		$this->assertSame('1.0.0', $plugin->version());
	}

	public function testVersionInvalid()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin-version-invalid'
		);

		$this->assertNull($plugin->version());
	}

	public function testVersionComposer()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin-composer',
			root: static::FIXTURES . '/plugin-version-composer'
		);

		$this->assertSame('5.2.3', $plugin->version());
	}

	public function testVersionComposerNoVersionSet()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin',
			root: static::FIXTURES . '/plugin-version-composer-noversionset'
		);

		$this->assertNull($plugin->version());
	}

	public function testVersionComposerOverride()
	{
		$plugin = new Plugin(
			name: 'getkirby/test-plugin-composer',
			root: static::FIXTURES . '/plugin-version-composer-override'
		);

		$this->assertSame('5.2.3', $plugin->version());
	}
}
