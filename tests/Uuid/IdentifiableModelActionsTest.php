<?php

namespace Kirby\Uuid;

class IdentifiableModelActionsTest extends TestCase
{
	public function testFileChangeName()
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

	public function testFileDelete()
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

	public function testPageChangeSlug()
	{
		$page = $this->app->page('page-a');
		$uuid = $page->uuid();
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$this->app->impersonate('kirby');
		$page->changeSlug('page-c');
		$this->assertFalse($uuid->isCached());
	}

	public function testPageDelete()
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
