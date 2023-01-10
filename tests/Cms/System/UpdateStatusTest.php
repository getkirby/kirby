<?php

namespace Kirby\Cms\System;

use Kirby\Cms\Plugin;
use Kirby\Data\Json;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Cms\System\UpdateStatus
 */
class UpdateStatusTest extends TestCase
{
	protected static $host;
	protected $data = [];
	protected $tmp = __DIR__ . '/tmp';

	public static function setUpBeforeClass(): void
	{
		static::$host = UpdateStatus::$host;
		UpdateStatus::$host = 'file://' . __DIR__ . '/fixtures/UpdateStatusTest/getkirby.com';
	}

	public static function tearDownAfterClass(): void
	{
		UpdateStatus::$host = static::$host;
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);
	}

	/**
	 * @covers ::loadData
	 */
	public function testLoadData()
	{
		$app = $this->app('88888.8.5');

		$updateStatus = new UpdateStatus($app);
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		$this->assertSame([
			'latest' => '99999.9.9',
			'versions' => [
				'99999.9.9' => [
					'description' => 'Latest Kirby release',
					'status' => 'latest'
				],
				'*' => [
					'description' => 'Actively supported',
					'latest' => '88888.8.8',
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://getkirby.com/releases/{{ version }}',
					'download' => 'https://repoofthefuture.com/{{ version }}.zip',
					'upgrade' => 'https://getkirby.com/releases/99999'
				]
			],
			'incidents' => [],
			'messages' => [],
			'_version' => '88888.8.5'
		], $app->cache('updates')->get('security'));
	}

	/**
	 * @covers ::loadData
	 */
	public function testLoadDataCacheKirby()
	{
		$app = $this->app('88888.8.5');
		$app->cache('updates')->set('security', [
			'_version' => '88888.8.5',
			'versions' => [
				'*' => [
					'latest' => '88888.8.6',
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://getkirby.com/releases/{{ version }}'
				]
			]
		]);

		$updateStatus = new UpdateStatus($app);
		$this->assertSame('88888.8.6', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		// cache shouldn't be used after an update
		MockApp::$version = '88888.8.6';

		$updateStatus = new UpdateStatus($app);
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		$this->assertSame([
			'latest' => '99999.9.9',
			'versions' => [
				'99999.9.9' => [
					'description' => 'Latest Kirby release',
					'status' => 'latest'
				],
				'*' => [
					'description' => 'Actively supported',
					'latest' => '88888.8.8',
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://getkirby.com/releases/{{ version }}',
					'download' => 'https://repoofthefuture.com/{{ version }}.zip',
					'upgrade' => 'https://getkirby.com/releases/99999'
				]
			],
			'incidents' => [],
			'messages' => [],
			'_version' => '88888.8.6'
		], $app->cache('updates')->get('security'));
	}

	/**
	 * @covers ::loadData
	 */
	public function testLoadDataCachePlugin()
	{
		$app = $this->app('88888.8.5');
		$app->cache('updates')->set('plugins/getkirby/public', [
			'_version' => '88888.8.5',
			'versions' => [
				'*' => [
					'latest' => '88888.8.6',
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://getkirby.com/releases/{{ version }}'
				]
			]
		]);

		$plugin = new Plugin('getkirby/public', [
			'info' => [
				'version' => '88888.8.5'
			]
		]);

		$updateStatus = new UpdateStatus($plugin);
		$this->assertSame('88888.8.6', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		// the Kirby version shouldn't matter
		MockApp::$version = '88888.8.6';

		$updateStatus = new UpdateStatus($plugin);
		$this->assertSame('88888.8.6', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		// but the plugin version does
		$plugin = new Plugin('getkirby/public', [
			'info' => [
				'version' => '88888.8.6'
			]
		]);

		$updateStatus = new UpdateStatus($plugin);
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		$this->assertSame([
			'latest' => '99999.9.9',
			'versions' => [
				'99999.9.9' => [
					'description' => 'Latest plugin release',
					'status' => 'latest'
				],
				'*' => [
					'description' => 'Actively supported',
					'latest' => '88888.8.8',
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://github.com/getkirby/public-plugin/releases/tag/{{ version }}',
					'download' => 'https://github.com/getkirby/public-plugin/archive/refs/tags/{{ version }}.zip',
					'upgrade' => 'https://getkirby.com/releases/99999'
				]
			],
			'incidents' => [],
			'messages' => [],
			'_version' => '88888.8.6'
		], $app->cache('updates')->get('plugins/getkirby/public'));
	}

	/**
	 * @covers ::loadData
	 */
	public function testLoadDataCacheInvalid()
	{
		$app = $this->app('88888.8.5');
		$app->cache('updates')->set('security', 12345);

		$updateStatus = new UpdateStatus($app);
		$this->assertSame('88888.8.8', $updateStatus->targetVersion());
		$this->assertSame([], $updateStatus->exceptionMessages());

		$this->assertSame([
			'latest' => '99999.9.9',
			'versions' => [
				'99999.9.9' => [
					'description' => 'Latest Kirby release',
					'status' => 'latest'
				],
				'*' => [
					'description' => 'Actively supported',
					'latest' => '88888.8.8',
					'status' => 'active-support'
				]
			],
			'urls' => [
				'*' => [
					'changes' => 'https://getkirby.com/releases/{{ version }}',
					'download' => 'https://repoofthefuture.com/{{ version }}.zip',
					'upgrade' => 'https://getkirby.com/releases/99999'
				]
			],
			'incidents' => [],
			'messages' => [],
			'_version' => '88888.8.5'
		], $app->cache('updates')->get('security'));
	}

	/**
	 * @covers ::loadData
	 */
	public function testLoadDataCacheDisabled()
	{
		$app = $this->app('88888.8.5')->clone([
			'options' => [
				'cache' => [
					'updates' => false
				]
			]
		]);

		$updateStatus = new UpdateStatus($app);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Cannot check for updates without a working "updates" cache'
		], $updateStatus->exceptionMessages());
	}

	/**
	 * @covers ::loadData
	 */
	public function testLoadDataNotFound()
	{
		$app    = $this->app('88888.8.8');
		$plugin = new Plugin('getkirby/test', [
			'info' => [
				'version' => '88888.8.5'
			]
		]);

		$updateStatus = new UpdateStatus($plugin);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin getkirby/test: Couldn\'t open file ' .
			__DIR__ . '/fixtures/UpdateStatusTest/getkirby.com/plugins/getkirby/test.json'
		], $updateStatus->exceptionMessages());

		$this->assertSame(
			'Could not load update data for plugin getkirby/test: Couldn\'t open file ' .
			__DIR__ . '/fixtures/UpdateStatusTest/getkirby.com/plugins/getkirby/test.json',
			$app->cache('updates')->get('plugins/getkirby/test')
		);

		// cached error should be used on subsequent requests
		$updateStatus = new UpdateStatus($plugin);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin getkirby/test: Couldn\'t open file ' .
			__DIR__ . '/fixtures/UpdateStatusTest/getkirby.com/plugins/getkirby/test.json'
		], $updateStatus->exceptionMessages());
	}

	/**
	 * @covers ::loadData
	 */
	public function testLoadDataNotJson()
	{
		$app    = $this->app('88888.8.8');
		$plugin = new Plugin('getkirby/invalid-json', [
			'info' => [
				'version' => '88888.8.5'
			]
		]);

		$updateStatus = new UpdateStatus($plugin);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin getkirby/invalid-json: Invalid JSON data'
		], $updateStatus->exceptionMessages());

		$this->assertSame(
			'Could not load update data for plugin getkirby/invalid-json: Invalid JSON data',
			$app->cache('updates')->get('plugins/getkirby/invalid-json')
		);

		// cached error should be used on subsequent requests
		$updateStatus = new UpdateStatus($plugin);
		$this->assertNull($updateStatus->targetVersion());
		$this->assertSame([
			'Could not load update data for plugin getkirby/invalid-json: Invalid JSON data'
		], $updateStatus->exceptionMessages());
	}

	/**
	 * @covers \Kirby\Cms\System\UpdateStatus
	 * @dataProvider logicProvider
	 */
	public function testLogic(
		string $packageType,
		string|null $version,
		bool $securityOnly,
		array|null $data,
		array $expected
	) {
		$package      = $this->$packageType($version);
		$updateStatus = new UpdateStatus($package, $securityOnly, $data);

		foreach ($expected as $method => $value) {
			$this->assertSame($value, $updateStatus->$method());
		}
	}

	public function logicProvider(): array
	{
		return [
			// update check (Kirby)
			'Kirby up-to-date' => [
				'app',
				'88888.8.8',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '88888.8.8',
					'icon' => 'check',
					'label' => 'Up to date',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'up-to-date',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://getkirby.com/releases/88888.8.8',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Kirby unreleased' => [
				'app',
				'88888.8.9-rc.1',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '88888.8.9-rc.1',
					'icon' => 'question',
					'label' => 'Unreleased version',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'unreleased',
					'targetVersion' => null,
					'theme' => 'notice',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Kirby security-update' => [
				'app',
				'77777.1.2',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '77777.1.2',
					'icon' => 'alert',
					'label' => 'Free security update 77777.5.5 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/old-releases/77777.5.5',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Kirby security-upgrade' => [
				'app',
				'55555.1.2',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '55555.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Kirby not-vulnerable (update "no-vulnerabilities")' => [
				'app',
				'77777.7.6',
				true,
				$this->data('basic'),
				[
					'currentVersion' => '77777.7.6',
					'icon' => 'check',
					'label' => 'No known vulnerabilities',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'not-vulnerable',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://getkirby.com/old-releases/77777.7.6',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Kirby not-vulnerable (update)' => [
				'app',
				'66666.6.5',
				true,
				$this->data('basic'),
				[
					'currentVersion' => '66666.6.5',
					'icon' => 'check',
					'label' => 'No known vulnerabilities',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'not-vulnerable',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://getkirby.com/old-releases/66666.6.5',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Kirby not-vulnerable (upgrade)' => [
				'app',
				'77777.7.7',
				true,
				$this->data('basic'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'check',
					'label' => 'No known vulnerabilities',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'not-vulnerable',
					'targetVersion' => null,
					'theme' => 'positive',
					'url' => 'https://getkirby.com/old-releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Kirby update' => [
				'app',
				'77777.7.6',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '77777.7.6',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://getkirby.com/old-releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Kirby upgrade' => [
				'app',
				'77777.7.7',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],

			// update check (plugin)
			'Plugin up-to-date' => [
				'plugin',
				'88888.8.8',
				false,
				$this->data('basic'),
				[
					'messages' => [],
					'status' => 'up-to-date',
					'targetVersion' => null,
					'toArray' => [
						'currentVersion' => '88888.8.8',
						'icon' => 'check',
						'label' => 'Up to date',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'positive',
						'url' => 'https://getkirby.com/releases/88888.8.8'
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin unreleased' => [
				'plugin',
				'88888.8.9-rc.1',
				false,
				$this->data('basic'),
				[
					'messages' => [],
					'status' => 'unreleased',
					'targetVersion' => null,
					'toArray' => [
						'currentVersion' => '88888.8.9-rc.1',
						'icon' => 'question',
						'label' => 'Unreleased version',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'notice',
						'url' => null
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin security-update' => [
				'plugin',
				'77777.1.2',
				false,
				$this->data('basic'),
				[
					'messages' => [
						[
							'text' => (
								'Your installation might be affected by the following vulnerability ' .
								'in the getkirby/test plugin (high severity): Some incident'
							),
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'toArray' => [
						'currentVersion' => '77777.1.2',
						'icon' => 'alert',
						'label' => 'Free security update 77777.5.5 available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'negative',
						'url' => 'https://getkirby.com/old-releases/77777.5.5',
					],
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Plugin security-upgrade' => [
				'plugin',
				'55555.1.2',
				false,
				$this->data('basic'),
				[
					'messages' => [
						[
							'text' => (
								'Your installation might be affected by the following vulnerability ' .
								'in the getkirby/test plugin (high severity): Some incident'
							),
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'toArray' => [
						'currentVersion' => '55555.1.2',
						'icon' => 'alert',
						'label' => 'Upgrade 88888.8.8 with security fixes available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'negative',
						'url' => 'https://getkirby.com/releases/88888',
					],
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Plugin update' => [
				'plugin',
				'77777.7.6',
				false,
				$this->data('basic'),
				[
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'toArray' => [
						'currentVersion' => '77777.7.6',
						'icon' => 'info',
						'label' => 'Free update 77777.7.7 available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'info',
						'url' => 'https://getkirby.com/old-releases/77777.7.7',
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin upgrade' => [
				'plugin',
				'77777.7.7',
				false,
				$this->data('basic'),
				[
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'toArray' => [
						'currentVersion' => '77777.7.7',
						'icon' => 'info',
						'label' => 'Upgrade 88888.8.8 available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'info',
						'url' => 'https://getkirby.com/releases/88888',
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin with prefix' => [
				'plugin',
				'v77777.7.7',
				false,
				$this->data('basic'),
				[
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'toArray' => [
						'currentVersion' => '77777.7.7',
						'icon' => 'info',
						'label' => 'Upgrade 88888.8.8 available',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'info',
						'url' => 'https://getkirby.com/releases/88888',
					],
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Plugin with invalid version' => [
				'plugin',
				'not a version',
				false,
				$this->data('basic'),
				[
					'messages' => null,
					'status' => 'error',
					'targetVersion' => null,
					'toArray' => [
						'currentVersion' => '?',
						'icon' => 'question',
						'label' => 'Could not check for updates',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'notice',
						'url' => 'https://getkirby.com/releases/88888.8.8',
					],
					'vulnerabilities' => null,
					'exceptionMessages' => []
				]
			],
			'Plugin without version' => [
				'plugin',
				null,
				false,
				$this->data('basic'),
				[
					'messages' => null,
					'status' => 'error',
					'targetVersion' => null,
					'toArray' => [
						'currentVersion' => '?',
						'icon' => 'question',
						'label' => 'Could not check for updates',
						'latestVersion' => '88888.8.8',
						'pluginName' => 'getkirby/test',
						'theme' => 'notice',
						'url' => 'https://getkirby.com/releases/88888.8.8',
					],
					'vulnerabilities' => null,
					'exceptionMessages' => []
				]
			],

			// vulnerabilities
			'Vulnerability sorting' => [
				'app',
				'77777.1.2',
				false,
				$this->data('incidents-severity'),
				[
					'currentVersion' => '77777.1.2',
					'icon' => 'alert',
					'label' => 'Free security update 77777.5.5 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (critical severity): Incident 5',
							'link' => 'https://getkirby.com/security/incident-5',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Incident 1',
							'link' => 'https://getkirby.com/security/incident-1',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installation might be affected by the following vulnerability (medium severity): Incident 4',
							'link' => 'https://getkirby.com/security/incident-4',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installation might be affected by the following vulnerability (low severity): Incident 3',
							'link' => 'https://getkirby.com/security/incident-3',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installation might be affected by the following vulnerability (invalid severity): Incident 2',
							'link' => 'https://getkirby.com/security/incident-2',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/releases/77777.5.5',
					'vulnerabilities' => [
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 5',
							'fixed' => '77777.5.5',
							'link' => 'https://getkirby.com/security/incident-5',
							'severity' => 'critical'
						],
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 1',
							'fixed' => '77777.5.5',
							'link' => 'https://getkirby.com/security/incident-1',
							'severity' => 'high'
						],
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 4',
							'fixed' => '77777.5.5',
							'link' => 'https://getkirby.com/security/incident-4',
							'severity' => 'medium'
						],
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 3',
							'fixed' => '77777.5.5',
							'link' => 'https://getkirby.com/security/incident-3',
							'severity' => 'low'
						],
						[
							'affected' => '<=77777.5.4',
							'description' => 'Incident 2',
							'fixed' => '77777.5.5',
							'link' => 'https://getkirby.com/security/incident-2',
							'severity' => 'invalid'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Unstable release' => [
				'app',
				'77777.0.0-rc.1',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '77777.0.0-rc.1',
					'icon' => 'alert',
					'label' => 'Free security update 77777.5.5 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/old-releases/77777.5.5',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Minimum security update' => [
				'app',
				'77777.1.2',
				false,
				$this->data('incidents-cascade'),
				[
					'currentVersion' => '77777.1.2',
					'icon' => 'alert',
					'label' => 'Free security update 77777.5.5 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Incident 1',
							'link' => 'https://getkirby.com/security/incident-1',
							'icon' => 'bug'
						]
					],
					'status' => 'security-update',
					'targetVersion' => '77777.5.5',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/releases/77777.5.5',
					'vulnerabilities' => [
						[
							'affected' => '<=77777.2.5 || 88888.0.0 - 88888.3.2',
							'description' => 'Incident 1',
							'fixed' => '77777.2.6, 88888.3.3',
							'link' => 'https://getkirby.com/security/incident-1',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'Incidents with infinite loop' => [
				'app',
				'77777.1.2',
				false,
				$this->data('incidents-loop'),
				[
					'currentVersion' => '77777.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 88888.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5, 88888.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],

			// messages
			'Messages' => [
				'app',
				'77777.6.0',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '77777.6.0',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Some message that matches',
							'kirby' => '77777.6.0',
							'php' => '*'
						]
					],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://getkirby.com/old-releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'EOL warning (Kirby)' => [
				'app',
				'44444.1.2',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '44444.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installed Kirby version has reached end-of-life and will not receive further security updates',
							'link' => 'https://getkirby.com/security/end-of-life',
							'icon' => 'bell'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'EOL warning (plugin)' => [
				'plugin',
				'44444.1.2',
				false,
				$this->data('basic'),
				[
					'currentVersion' => '44444.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability in the getkirby/test plugin (high severity): Some incident',
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installed version of the getkirby/test plugin is has reached end-of-life and will not receive further security updates',
							'link' => 'https://getkirby.com/security/end-of-life',
							'icon' => 'bell'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],
			'EOL warning with custom link' => [
				'app',
				'44444.1.2',
				false,
				$this->data('eol-link'),
				[
					'currentVersion' => '44444.1.2',
					'icon' => 'alert',
					'label' => 'Upgrade 88888.8.8 with security fixes available',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						],
						[
							'text' => 'Your installed Kirby version has reached end-of-life and will not receive further security updates',
							'link' => 'https://getkirby.com/44444-is-eol',
							'icon' => 'bell'
						]
					],
					'status' => 'security-upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'negative',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => []
				]
			],

			// invalid/incomplete data array
			'No data' => [
				'plugin',
				'77777.7.7',
				false,
				null,
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => null,
					'messages' => null,
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'notice',
					'url' => null,
					'vulnerabilities' => null,
					'exceptionMessages' => [
						'Could not load update data for plugin getkirby/test: Couldn\'t open file ' .
						__DIR__ . '/fixtures/UpdateStatusTest/getkirby.com/plugins/getkirby/test.json'
					]
				]
			],
			'Empty data' => [
				'app',
				'77777.7.7',
				false,
				[],
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => null,
					'messages' => [],
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'notice',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching version entry found for Kirby@77777.7.7',
						'No matching URL found for Kirby@77777.7.7'
					]
				]
			],
			'No latest version (update)' => [
				'app',
				'77777.7.5',
				false,
				$this->data('no-latest'),
				[
					'currentVersion' => '77777.7.5',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => null,
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'No latest version (upgrade)' => [
				'app',
				'77777.7.7',
				false,
				$this->data('no-latest'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade ? available',
					'latestVersion' => null,
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => null,
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'No versions' => [
				'app',
				'77777.7.7',
				false,
				$this->data('no-versions'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'notice',
					'url' => 'https://getkirby.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching version entry found for Kirby@77777.7.7'
					]
				]
			],
			'No URLs' => [
				'app',
				'77777.7.7',
				false,
				$this->data('no-urls'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching URL found for Kirby@77777.7.7'
					]
				]
			],
			'No incidents' => [
				'app',
				'77777.7.7',
				false,
				$this->data('no-incidents'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'No messages' => [
				'app',
				'77777.7.7',
				false,
				$this->data('no-messages'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Missing URL entry' => [
				'app',
				'77777.7.7',
				false,
				$this->data('no-url-entry'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching URL found for Kirby@77777.7.7'
					]
				]
			],
			'URL entry without `changes` key' => [
				'app',
				'77777.7.7',
				false,
				$this->data('url-entry-without-changes'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => null,
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching URL found for Kirby@77777.7.7'
					]
				]
			],
			'Missing version entry' => [
				'app',
				'77777.7.7',
				false,
				$this->data('no-version-entry'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'notice',
					'url' => 'https://getkirby.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'No matching version entry found for Kirby@77777.7.7'
					]
				]
			],
			'Missing version entry (with vulnerability)' => [
				'app',
				'77777.4.3',
				false,
				$this->data('no-version-entry'),
				[
					'currentVersion' => '77777.4.3',
					'icon' => 'question',
					'label' => 'Could not check for updates',
					'latestVersion' => '88888.8.8',
					'messages' => [
						[
							'text' => 'Your installation might be affected by the following vulnerability (high severity): Some incident',
							'link' => 'https://getkirby.com/security/some-incident',
							'icon' => 'bug'
						]
					],
					'status' => 'error',
					'targetVersion' => null,
					'theme' => 'notice',
					'url' => 'https://getkirby.com/releases/77777.4.3',
					'vulnerabilities' => [
						[
							'affected' => '<=66666.5.4 || 77777.0.0 - 77777.5.4',
							'description' => 'Some incident',
							'fixed' => '66666.5.5, 77777.5.5',
							'link' => 'https://getkirby.com/security/some-incident',
							'severity' => 'high'
						]
					],
					'exceptionMessages' => [
						'No matching version entry found for Kirby@77777.4.3'
					]
				]
			],
			'Version entry without `latest` key' => [
				'app',
				'77777.7.5',
				false,
				$this->data('version-entry-without-latest'),
				[
					'currentVersion' => '77777.7.5',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => []
				]
			],
			'Invalid constraint (message)' => [
				'app',
				'77777.7.7',
				false,
				$this->data('invalid-constraint-message'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Error comparing version constraint for Kirby while filtering messages: ' .
						'Could not parse version constraint 77777.6.0-77777.7.9: Invalid version string "77777.6.0-77777.7.9"',
						'Error comparing version constraint for Kirby while filtering messages: ' .
						'Could not parse version constraint 7.4-8.2: Invalid version string "7.4-8.2"'
					]
				]
			],
			'Invalid constraint (incident)' => [
				'app',
				'77777.3.2',
				false,
				$this->data('invalid-constraint-incident'),
				[
					'currentVersion' => '77777.3.2',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Error comparing version constraint for Kirby while filtering incidents: ' .
						'Could not parse version constraint 77777.0.0-77777.5.4: Invalid version string "77777.0.0-77777.5.4"'
					]
				]
			],
			'Invalid constraint (URL entry)' => [
				'app',
				'77777.7.5',
				false,
				$this->data('invalid-constraint-url'),
				[
					'currentVersion' => '77777.7.5',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://getkirby.com/old-releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Error comparing version constraint for Kirby while finding URL: ' .
						'Could not parse version constraint invalid: Invalid version string "invalid"'
					]
				]
			],
			'Invalid constraint (version entry)' => [
				'app',
				'77777.7.5',
				false,
				$this->data('invalid-constraint-version'),
				[
					'currentVersion' => '77777.7.5',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Error comparing version constraint for Kirby while finding version entry: ' .
						'Could not parse version constraint invalid: Invalid version string "invalid"'
					]
				]
			],
			'Missing constraint (message)' => [
				'app',
				'77777.7.7',
				false,
				$this->data('missing-constraint-message'),
				[
					'currentVersion' => '77777.7.7',
					'icon' => 'info',
					'label' => 'Upgrade 88888.8.8 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'upgrade',
					'targetVersion' => '88888.8.8',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/88888',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Missing constraint kirby for Kirby while filtering messages',
						'Missing constraint php for Kirby while filtering messages'
					]
				]
			],
			'Missing constraint (incident)' => [
				'app',
				'77777.3.2',
				false,
				$this->data('missing-constraint-incident'),
				[
					'currentVersion' => '77777.3.2',
					'icon' => 'info',
					'label' => 'Free update 77777.7.7 available',
					'latestVersion' => '88888.8.8',
					'messages' => [],
					'status' => 'update',
					'targetVersion' => '77777.7.7',
					'theme' => 'info',
					'url' => 'https://getkirby.com/releases/77777.7.7',
					'vulnerabilities' => [],
					'exceptionMessages' => [
						'Missing constraint affected for Kirby while filtering incidents'
					]
				]
			]
		];
	}

	/**
	 * @covers ::messages
	 */
	public function testMessagesCache()
	{
		$updateStatus = new UpdateStatus($this->app('77777.6.0'), false, $this->data('basic'));

		$expected = [
			[
				'text' => 'Some message that matches',
				'kirby' => '77777.6.0',
				'php' => '*'
			]
		];

		$this->assertSame($expected, $updateStatus->messages());

		// cached result should be the same
		$this->assertSame($expected, $updateStatus->messages());
	}

	protected function app(string $version): MockApp
	{
		MockApp::$version = $version;
		return new MockApp([
			'roots' => [
				'index' => $this->tmp
			]
		]);
	}

	protected function data(string $name): array
	{
		if (isset($this->data[$name]) === true) {
			return $this->data[$name];
		}

		$path = __DIR__ . '/fixtures/UpdateStatusTest/logic/' . $name . '.json';
		return $this->data[$name] = Json::read($path);
	}

	protected function plugin(string|null $version): Plugin
	{
		// ensure a global app object is initialized for message filtering
		$this->app('88888.8.8');

		return new Plugin('getkirby/test', [
			'info' => [
				'version' => $version
			]
		]);
	}
}
