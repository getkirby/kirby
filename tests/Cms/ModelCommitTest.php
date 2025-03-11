<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversDefaultClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversDefaultClass(ModelCommit::class)]
class ModelCommitTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.ModelCommit';

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpTmp();
	}

	public function tearDown(): void
	{
		parent::tearDown();
		$this->tearDownTmp();
	}

	public static function modelProvider(): array
	{
		return [
			[
				new File(['parent' => new Site(), 'filename' => 'test.jpg']),
				FileRules::class
			],
			[
				new Page(['slug' => 'test']),
				PageRules::class
			],
			[
				new Site(),
				SiteRules::class
			],
			[
				new User(['email' => 'test@test.com']),
				UserRules::class
			],
		];
	}

	public function testAfter()
	{
		$phpunit = $this;
		$calls   = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.test:after' => function (Page $newPage, Page $oldPage) use ($phpunit, &$calls) {
					$phpunit->assertSame('test-modified', $newPage->slug());
					$phpunit->assertSame('test', $oldPage->slug());
					$calls++;
				}
			]
		]);

		$oldPage = new Page([
			'slug' => 'test',
		]);

		$newPage = new Page([
			'slug' => 'test-modified',
		]);

		$commit = new ModelCommit(
			model: $oldPage,
			action: 'test'
		);

		$result = $commit->after($newPage);

		$this->assertSame($newPage, $result);
		$this->assertSame(1, $calls);
	}

	public function testAfterFlushesCache()
	{
		$this->app = $this->app->clone([
			'options' => [
				'cache' => [
					'pages' => true
				]
			]
		]);

		$page = new Page([
			'slug' => 'test',
		]);

		// set a dummy cache entry
		$this->app->cache('pages')->set('test', 'test');

		$this->assertSame('test', $this->app->cache('pages')->get('test'), 'Make sure that the cache is actually set');

		$commit = new ModelCommit(
			model: $page,
			action: 'delete'
		);

		$commit->after($page);

		$this->assertSame(null, $this->app->cache('pages')->get('test'));
	}

	public function testAfterHookArgumentsForPageCreate()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$commit = new ModelCommit(
			model: $page,
			action: 'create'
		);

		$args = $commit->afterHookArguments(
			state: $page
		);

		$this->assertSame([
			'page' => $page
		], $args);
	}

	public function testAfterHookArgumentsForPageDuplicate()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$copy = new Page([
			'slug' => 'test-copy',
		]);

		$commit = new ModelCommit(
			model: $page,
			action: 'duplicate'
		);

		$args = $commit->afterHookArguments(
			state: $copy
		);

		$this->assertSame([
			'duplicatePage' => $copy,
			'originalPage'  => $page
		], $args);
	}

	public function testAfterHookArgumentsForPageDelete()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$commit = new ModelCommit(
			model: $page,
			action: 'delete'
		);

		$args = $commit->afterHookArguments(
			state: true
		);

		$this->assertSame([
			'status' => true,
			'page'   => $page
		], $args);
	}

	public function testAfterHookArgumentsForPageUpdate()
	{
		$oldPage = new Page([
			'slug' => 'test',
		]);

		$newPage = new Page([
			'slug' => 'test',
		]);

		$commit = new ModelCommit(
			model: $oldPage,
			action: 'update'
		);

		$args = $commit->afterHookArguments(
			state: $newPage
		);

		$this->assertSame([
			'newPage' => $newPage,
			'oldPage' => $oldPage
		], $args);
	}

	public function testAfterHookArgumentsForFileCreate()
	{
		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.txt',
		]);

		$commit = new ModelCommit(
			model: $file,
			action: 'create'
		);

		$args = $commit->afterHookArguments(
			state: $file
		);

		$this->assertSame([
			'file' => $file
		], $args);
	}

	public function testAfterHookArgumentsForFileDelete()
	{
		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.txt',
		]);

		$commit = new ModelCommit(
			model: $file,
			action: 'delete'
		);

		$args = $commit->afterHookArguments(
			state: true
		);

		$this->assertSame([
			'status' => true,
			'file'   => $file
		], $args);
	}

	public function testAfterHookArgumentsForFileUpdate()
	{
		$oldFile = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.txt',
		]);

		$newFile = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.txt',
		]);

		$commit = new ModelCommit(
			model: $oldFile,
			action: 'update'
		);

		$args = $commit->afterHookArguments(
			state: $newFile
		);

		$this->assertSame([
			'newFile' => $newFile,
			'oldFile' => $oldFile
		], $args);
	}

	public function testAfterHookArgumentsForSiteActions()
	{
		$oldSite = new Site([
			'name' => 'Test'
		]);

		$newSite = new Site([
			'name' => 'Test'
		]);

		$commit = new ModelCommit(
			model: $oldSite,
			action: 'update'
		);

		$args = $commit->afterHookArguments(
			state: $newSite
		);

		$this->assertSame([
			'newSite' => $newSite,
			'oldSite' => $oldSite
		], $args);
	}

	public function testAfterHookArgumentsForUserCreate()
	{
		$user = new User([
			'email' => 'test@test.com'
		]);

		$commit = new ModelCommit(
			model: $user,
			action: 'create'
		);

		$args = $commit->afterHookArguments(
			state: $user
		);

		$this->assertSame([
			'user' => $user
		], $args);
	}

	public function testAfterHookArgumentsForUserDelete()
	{
		$user = new User([
			'email' => 'test@test.com'
		]);

		$commit = new ModelCommit(
			model: $user,
			action: 'delete'
		);

		$args = $commit->afterHookArguments(
			state: true
		);

		$this->assertSame([
			'status' => true,
			'user'   => $user
		], $args);
	}

	public function testAfterHookArgumentsForUserUpdate()
	{
		$oldUser = new User([
			'email' => 'test@test.com'
		]);

		$newUser = new User([
			'email' => 'test@test.com'
		]);

		$commit = new ModelCommit(
			model: $oldUser,
			action: 'update'
		);

		$args = $commit->afterHookArguments(
			state: $newUser
		);

		$this->assertSame([
			'newUser' => $newUser,
			'oldUser' => $oldUser
		], $args);
	}

	public function testBefore()
	{
		$phpunit = $this;
		$calls   = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.test:before' => function (Page $page) use ($phpunit, &$calls) {
					$phpunit->assertSame('test', $page->slug());
					$calls++;
				}
			]
		]);

		$page = new Page([
			'slug' => 'test',
		]);

		$commit = new ModelCommit(
			model: $page,
			action: 'test'
		);

		$result = $commit->before(arguments: [
			'page' => $page
		]);

		$this->assertSame([
			'page' => $page
		], $result);

		$this->assertSame(1, $calls);
	}

	public function testHook()
	{
		$phpunit = $this;
		$calls   = 0;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.test:before' => function ($a, $b) use ($phpunit, &$calls) {
					$phpunit->assertSame('Argument A', $a);
					$phpunit->assertSame('Argument B', $b);

					$calls++;
					return $a . ' modified';
				}
			]
		]);

		$page = new Page([
			'slug' => 'test',
		]);

		$commit = new ModelCommit(
			model: $page,
			action: 'test'
		);

		$arguments = [
			'a' => 'Argument A',
			'b' => 'Argument B'
		];

		$result = $commit->hook(
			hook: 'before',
			arguments: $arguments
		);

		$this->assertSame([
			'arguments' => [
				'a' => 'Argument A modified',
				'b' => 'Argument B',
			],
			'result'  => 'Argument A modified',
		], $result);

		$this->assertSame(1, $calls);
	}

	#[DataProvider('modelProvider')]
	public function testRules(ModelWithContent $model, string $rulesClass)
	{
		$commit = new ModelCommit(
			model: $model,
			action: 'create'
		);

		$rules = $commit->rules();

		$this->assertInstanceOf($rulesClass, $rules);
	}
}
