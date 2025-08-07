<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelState::class)]
class ModelStateTest extends TestCase
{
	public function testUpdateFile(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'test.jpg',
						'content' => [
							'alt' => 'Current'
						]
					]
				]
			]
		]);

		$parent  = $this->app->site();
		$current = $this->app->file('test.jpg');
		$next    = new File([
			'filename' => 'test.jpg',
			'parent' => $parent,
			'content' => [
				'alt' => 'Next'
			]
		]);

		ModelState::update(
			method: 'update',
			current: $current,
			next: $next
		);

		$this->assertSame($next, $parent->file('test.jpg'));
	}

	public function testUpdateFileWithDuplicateAsMethod(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'test.jpg',
						'content' => [
							'alt' => 'Current'
						]
					]
				]
			]
		]);

		$parent  = $this->app->site();
		$current = $this->app->file('test.jpg');
		$next    = new File([
			'filename' => 'test.jpg',
			'parent' => $parent,
			'content' => [
				'alt' => 'Next'
			]
		]);

		ModelState::update(
			method: 'duplicate',
			current: $current,
			next: $next
		);

		$this->assertSame($current, $parent->file('test.jpg'));
	}

	public function testUpdatePage(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'title' => 'Current'
						]
					]
				]
			]
		]);

		$current = $this->app->page('test');
		$next    = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Next'
			]
		]);

		ModelState::update(
			method: 'changeTitle',
			current: $current,
			next: $next
		);

		$this->assertSame($next, $this->app->page('test'));
	}

	public function testUpdatePageWithDuplicateAsMethod(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'title' => 'Current'
						]
					]
				]
			]
		]);

		$current = $this->app->page('test');
		$next    = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Next'
			]
		]);

		ModelState::update(
			method: 'duplicate',
			current: $current,
			next: $next
		);

		$this->assertSame($current, $this->app->page('test'));
	}

	public function testUpdateSite(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'content' => [
					'title' => 'Current'
				]
			]
		]);

		$next = new Site([
			'content' => [
				'title' => 'Next'
			]
		]);

		ModelState::update(
			method: 'changeTitle',
			current: $this->app->site(),
			next: $next
		);

		$this->assertSame($this->app->site(), $next);
	}

	public function testUpdateSiteWithDuplicateAsMethod(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'content' => [
					'title' => 'Current'
				]
			]
		]);

		$current = $this->app->site();
		$next    = new Site([
			'content' => [
				'title' => 'Next'
			]
		]);

		ModelState::update(
			method: 'duplicate',
			current: $current,
			next: $next
		);

		$this->assertSame($current, $this->app->site());
	}

	public function testUpdateUser(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'admin',
					'email' => 'current@example.com'
				]
			]
		]);

		$next = new User([
			'id'    => 'admin',
			'email' => 'next@example.com'
		]);

		ModelState::update(
			method: 'changeEmail',
			current: $this->app->user('admin'),
			next: $next
		);

		$this->assertSame($this->app->user('admin'), $next);
	}

	public function testUpdateUserWithDuplicateAsMethod(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'admin',
					'email' => 'current@example.com'
				]
			]
		]);

		$current = $this->app->user('admin');
		$next    = new User([
			'id'    => 'admin',
			'email' => 'next@example.com'
		]);

		ModelState::update(
			method: 'duplicate',
			current: $current,
			next: $next
		);

		$this->assertSame($current, $this->app->user('admin'));
	}
}
