<?php

namespace Kirby;

use Kirby\Cms\App;
use Kirby\Cms\Core;
use Kirby\Filesystem\Dir;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use ReflectionFunction;

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
		// ensure the temp directory is there
		Dir::make(KIRBY_TMP_DIR);

		// delete the temp directory again after the tests
		$facade->registerSubscriber(new TempCleanupSubscriber());
	}

	public static function init(): void
	{
		// determine a unique path to a temporary directory
		$tmpDir = __DIR__ . '/tmp';

		// when running via ParaTest, use a separate directory for each process
		if (getenv('UNIQUE_TEST_TOKEN') !== false) {
			$tmpDir .= '/' . getenv('UNIQUE_TEST_TOKEN');
		}

		define('KIRBY_DIR', dirname(__DIR__));
		define('KIRBY_TMP_DIR', $tmpDir);
		define('KIRBY_TESTING', true);

		// disable Whoops for all tests that don't need it
		// to reduce the impact of memory leaks
		App::$enableWhoops = false;

		// prevent PHPUnit tests from accessing files outside the repo
		Core::$indexRoot = '/dev/null';

		// check if the `dump()` helper was overridden, e.g. by Herd
		$dump = new ReflectionFunction('dump');
		define('KIRBY_DUMP_OVERRIDDEN', str_starts_with($dump->getFileName(), dirname(__DIR__)) === false);
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
