<?php

namespace Kirby\Uuid;

class IdentifiableModelActionsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Uuid.IdentifiableModelActions';

	public function testFileChangeName(): void
	{
		$file = $this->app->file('page-a/test.pdf');
		$uuid = $file->uuid();
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$this->app->impersonate('kirby');
		$file->changeName('foo.pdf');
		$this->assertFalse($uuid->isCached());
	}

	public function testFileDelete(): void
	{
		$file = $this->app->file('page-a/test.pdf');
		$uuid = $file->uuid();
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$this->app->impersonate('kirby');
		$file->delete();
		$this->assertFalse($uuid->isCached());
	}

	public function testPageChangeSlug(): void
	{
		$page = $this->app->page('page-a');
		$uuid = $page->uuid();
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$this->app->impersonate('kirby');
		$page->changeSlug('page-c');
		$this->assertTrue($uuid->isCached());
	}

	public function testPageDelete(): void
	{
		$page = $this->app->page('page-b');
		$uuid = $page->uuid();
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$this->app->impersonate('kirby');
		$page->delete();
		$uuid = $page->uuid();
		$this->assertFalse($uuid->isCached());
	}
}
