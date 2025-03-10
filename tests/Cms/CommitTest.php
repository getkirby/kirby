<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversDefaultClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversDefaultClass(Commit::class)]
class CommitTest extends TestCase
{
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

	public function testAfterHookArgumentsForPageCreate()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$commit = new Commit($page, 'create');
		$args   = $commit->afterHookArgumentsForPageActions($page, 'create', $page);

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

		$commit = new Commit($page, 'duplicate');
		$args   = $commit->afterHookArgumentsForPageActions($page, 'duplicate', $copy);

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

		$commit = new Commit($page, 'delete');
		$args   = $commit->afterHookArgumentsForPageActions($page, 'delete', true);

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

		$commit = new Commit($oldPage, 'update');
		$args   = $commit->afterHookArgumentsForPageActions($oldPage, 'update', $newPage);

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

		$commit = new Commit($file, 'create');
		$args   = $commit->afterHookArgumentsForFileActions($file, 'create', $file);

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

		$commit = new Commit($file, 'delete');
		$args   = $commit->afterHookArgumentsForFileActions($file, 'delete', true);

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

		$commit = new Commit($oldFile, 'update');
		$args   = $commit->afterHookArgumentsForFileActions($oldFile, 'update', $newFile);

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

		$commit = new Commit($oldSite, 'update');
		$args   = $commit->afterHookArgumentsForSiteActions($oldSite, 'update', $newSite);

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

		$commit = new Commit($user, 'create');
		$args   = $commit->afterHookArgumentsForUserActions($user, 'create', $user);

		$this->assertSame([
			'user' => $user
		], $args);
	}

	public function testAfterHookArgumentsForUserDelete()
	{
		$user = new User([
			'email' => 'test@test.com'
		]);

		$commit = new Commit($user, 'delete');
		$args   = $commit->afterHookArgumentsForUserActions($user, 'delete', true);

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

		$commit = new Commit($oldUser, 'update');
		$args   = $commit->afterHookArgumentsForUserActions($oldUser, 'update', $newUser);

		$this->assertSame([
			'newUser' => $newUser,
			'oldUser' => $oldUser
		], $args);
	}

	public function testHook()
	{
		$phpunit = $this;

		$this->app = $this->app->clone([
			'hooks' => [
				'page.test:before' => function ($a, $b) use ($phpunit) {
					$phpunit->assertSame('Argument A', $a);
					$phpunit->assertSame('Argument B', $b);

					return $a . ' modified';
				}
			]
		]);

		$page = new Page([
			'slug' => 'test',
		]);

		$commit = new Commit($page, 'test');
		$input  = [
			'a' => 'Argument A',
			'b' => 'Argument B'
		];

		$result = $commit->hook('before', $input);

		$this->assertSame([
			'arguments' => [
				'a' => 'Argument A modified',
				'b' => 'Argument B',
			],
			'result'  => 'Argument A modified',
		], $result);
	}

	#[DataProvider('modelProvider')]
	public function testRules(ModelWithContent $model, string $rulesClass)
	{
		$commit = new Commit($model, 'create');
		$rules  = $commit->rules();

		$this->assertInstanceOf($rulesClass, $rules);
	}
}
