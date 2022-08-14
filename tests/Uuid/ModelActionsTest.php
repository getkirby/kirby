<?php

namespace Kirby\Uuid;

class ModelActionsTest extends TestCase
{
	public function testFileChangeName()
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'a.jpg',
						'content'  => ['uuid' => 'my-file']
					]
				]
			]
		]);

		$file = $app->file('a.jpg');
		$uuid = Uuid::for('file://my-file');
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$app->impersonate('kirby');
		$file->changeName('b.jpg');
		$uuid = Uuid::for('file://my-file');
		$this->assertFalse($uuid->isCached());
	}

	public function testFileDelete()
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'a.jpg',
						'content'  => ['uuid' => 'my-file']
					]
				]
			]
		]);

		$file = $app->file('a.jpg');
		$uuid = Uuid::for('file://my-file');
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$app->impersonate('kirby');
		$file->delete();
		$uuid = Uuid::for('file://my-file');
		$this->assertFalse($uuid->isCached());
	}

	public function testPageChangeSlug()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id']
					]
				]
			]
		]);

		$page = $app->page('a');
		$uuid = $page->uuid();
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$app->impersonate('kirby');
		$page->changeSlug('b');
		$uuid = Uuid::for('page://my-id');
		$this->assertFalse($uuid->isCached());
	}

	public function testPageDelete()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => ['uuid' => 'my-id']
					]
				]
			]
		]);

		$page = $app->page('a');
		$uuid = $page->uuid();
		$this->assertFalse($uuid->isCached());
		$uuid->populate();
		$this->assertTrue($uuid->isCached());

		$app->impersonate('kirby');
		$page->delete();
		$uuid = Uuid::for('page://my-id');
		$this->assertFalse($uuid->isCached());
	}
}
