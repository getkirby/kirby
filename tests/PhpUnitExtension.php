<?php

namespace Kirby;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * PHPUnit extension to bootstrap the tests and
 * manage the temporary directory during testing
 *
 * @author Lukas Bestle <lukas@getkirby.com>
 * @link https://getkirby.com
 * @copyright Bastian Allgeier
 * @license https://opensource.org/licenses/MIT
 */
final class PhpUnitExtension implements Extension
{
	public function bootstrap(
		Configuration $configuration,
		Facade $facade,
		ParameterCollection $parameters
	): void {
		// disable Whoops for all tests that don't need it
		// to reduce the impact of memory leaks
		App::$enableWhoops = false;

		// ensure the temp directory is there
		Dir::make(KIRBY_TMP_DIR);

		// delete the temp directory again after the tests
		$facade->registerSubscriber(new TempCleanupSubscriber());
	}

	public static function defineConstants(): void
	{
		// determine a unique path to a temporary directory
		$tempDir = __DIR__ . '/tmp';

		// when running via ParaTest, use a separate directory for each process
		if (getenv('UNIQUE_TEST_TOKEN') !== false) {
			$tempDir .= '/' . getenv('UNIQUE_TEST_TOKEN');
		}

		define('KIRBY_TMP_DIR', $tempDir);
		define('KIRBY_TESTING', true);
	}
}

/**
 * PHPUnit event subscriber to be executed after all tests have been run
 *
 * @author Lukas Bestle <lukas@getkirby.com>
 * @link https://getkirby.com
 * @copyright Bastian Allgeier
 * @license https://opensource.org/licenses/MIT
 */
final class TempCleanupSubscriber implements ExecutionFinishedSubscriber
{
	public function notify(ExecutionFinished $event): void
	{
		Dir::remove(KIRBY_TMP_DIR);
	}
}
