<?php

namespace Kirby\Cms;

use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelCommit::class)]
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

	public function testHookWithModifiedModel()
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'test',
						'content' => [
							'title' => 'Original'
						]
					]
				]
			],
			'hooks' => [
				'page.test:after' => function (Page $page) {
					return $page->update([
						'title' => 'Modified'
					]);
				}
			]
		]);

		// needed to make the update call in the hook work
		$this->app->impersonate('kirby');

		// get the page from the app state
		$page = $this->app->page('test');

		$commit = new ModelCommit(
			model: $page,
			action: 'test'
		);

		$state = $commit->hook(
			hook: 'after',
			arguments: [
				'page' => $page
			]
		);

		$this->assertSame('Original', $page->title()->value(), 'The original model should not be modified');
		$this->assertSame('Modified', $state['result']->title()->value(), 'The result should be the modified model');
		$this->assertSame('Modified', $this->app->page('test')->title()->value(), 'The app state should be updated as well');
	}

	public function testHookWithModifiedModelLegacyMethod()
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'test',
						'content' => [
							'title' => 'Original'
						]
					]
				]
			],
			'hooks' => [
				'page.test:after' => function (Page $page) {
					// This is the legacy method to modify
					// the model directly. It should not
					// be used anymore and the model should be
					// returned instead
					$page->update([
						'title' => 'Modified'
					]);
				}
			]
		]);

		// needed to make the update call in the hook work
		$this->app->impersonate('kirby');

		// get the page from the app state
		$page = $this->app->page('test');

		$commit = new ModelCommit(
			model: $page,
			action: 'test'
		);

		$state = $commit->hook(
			hook: 'after',
			arguments: [
				'page' => $page
			]
		);

		$this->assertSame('Original', $page->title()->value(), 'The original model should not be modified');
		$this->assertSame('Modified', $state['result']->title()->value(), 'The result should be the modified model');
		$this->assertSame('Modified', $this->app->page('test')->title()->value(), 'The app state should be updated as well');
	}

	public function testHookWithMultipleHandlers()
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'test',
						'content' => [
							'title' => 'Original'
						]
					]
				]
			],
			'hooks' => [
				'page.test:after' => [
					function (Page $page) {
						return $page->update([
							'title' => 'Modified Title'
						]);
					},
					function (Page $page) {
						return $page->update([
							'subtitle' => 'Modified Subtitle'
						]);
					}
				]
			]
		]);

		// needed to make the update call in the hook work
		$this->app->impersonate('kirby');

		// get the page from the app state
		$page = $this->app->page('test');

		$commit = new ModelCommit(
			model: $page,
			action: 'test'
		);

		$state = $commit->hook(
			hook: 'after',
			arguments: [
				'page' => $page
			]
		);

		// the original model should not be modified
		$this->assertSame('Original', $page->title()->value());
		$this->assertSame(null, $page->subtitle()->value());

		// the result and the app state should be have the updated title and subtitle
		$this->assertSame('Modified Title', $state['result']->title()->value());
		$this->assertSame('Modified Subtitle', $state['result']->subtitle()->value());
		$this->assertSame('Modified Title', $this->app->page('test')->title()->value());
		$this->assertSame('Modified Subtitle', $this->app->page('test')->subtitle()->value());
	}

	public function testValidate(): void
	{
		$page   = new Page(['slug' => 'test']);
		$commit = new ModelCommit(
			model: $page,
			action: 'create'
		);

		$this->expectException(PermissionException::class);

		$commit->validate(arguments: [
			'page' => $page
		]);
	}
}
