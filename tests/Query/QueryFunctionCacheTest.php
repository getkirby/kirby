<?php

namespace Kirby\Query;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;

/**
 * @coversNothing
 */
class QueryFunctionCacheTest extends \PHPUnit\Framework\TestCase
{
	public function testCacheFunction()
	{
		$cache = App::instance()->cache('queries');
		$query = new Query('cache("my.key", () => foo.bar)');
		$data  = ['foo' => ['bar' => 'homer']];

		$this->assertFalse($cache->exists('my.key'));
		$this->assertSame('homer', $query->resolve($data));
		$this->assertTrue($cache->exists('my.key'));
		$this->assertSame('homer', $cache->get('my.key'));
		$cache->flush();
	}

	public function testFlushOnModelActions()
	{
		$app = new App([
			'roots' => [
				'index' => $tmp = __DIR__ . '/tmp'
			],
			'site' => [
				'children' => [
					['slug' => 'page-a'],
					['slug' => 'page-b'],
					['slug' => 'seite-c']
				]
			],
		]);

		$app->impersonate('kirby');
		$cache = $app->cache('queries');
		$cache->flush();
		Dir::make($tmp);

		$query = new Query('cache("my.key", () => kirby.site.index.filterBy("slug", "^=", "page-"))');

		$this->assertFalse($cache->exists('my.key'));
		$this->assertSame(2, $query->resolve()->count());
		$this->assertTrue($cache->exists('my.key'));

		$app->page('page-b')->delete();
		$this->assertFalse($cache->exists('my.key'));

		Dir::remove($tmp);
	}
}
