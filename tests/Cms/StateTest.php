<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversDefaultClass;

#[CoversDefaultClass(State::class)]
class StateTest extends TestCase
{
	public function testArgs()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertEquals([$page->id(), $page], State::args($page, 'changeSlug'));
		$this->assertEquals([$page->id(), $page], State::args($page, 'changeTitle'));
	}

	public function testArgsForRemoveAction()
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertEquals([$page], State::args($page, 'remove'));
	}

	public function testNormalizeMethod()
	{
		$this->assertEquals('append', State::normalizeMethod('append'));
		$this->assertEquals('append', State::normalizeMethod('create'));

		$this->assertEquals('remove', State::normalizeMethod('remove'));
		$this->assertEquals('remove', State::normalizeMethod('delete'));

		$this->assertEquals('set', State::normalizeMethod('changeTitle'));
		$this->assertEquals('set', State::normalizeMethod('changeSlug'));

		$this->assertEquals(false, State::normalizeMethod('duplicate'));
		$this->assertEquals(false, State::normalizeMethod(false));
	}

	public function testUpdateFile()
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

		State::updateFile(
			method: 'update',
			current: $current,
			next: $next
		);

		$this->assertSame($next, $parent->file('test.jpg'));
	}

	public function testUpdateFileWithFalseAsMethod()
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

		State::updateFile(
			method: false,
			current: $current,
			next: $next
		);

		$this->assertSame($current, $parent->file('test.jpg'));
	}

	public function testUpdatePage()
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

		State::updatePage(
			method: 'changeTitle',
			current: $current,
			next: $next
		);

		$this->assertSame($next, $this->app->page('test'));
	}

	public function testUpdatePageWithFalseAsMethod()
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

		State::updatePage(
			method: false,
			current: $current,
			next: $next
		);

		$this->assertSame($current, $this->app->page('test'));
	}

	public function testUpdateSite()
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

		State::updateSite(
			method: 'changeTitle',
			current: $this->app->site(),
			next: $next
		);

		$this->assertSame($this->app->site(), $next);
	}

	public function testUpdateSiteWithFalseAsMethod()
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

		State::updateSite(
			method: false,
			current: $current,
			next: $next
		);

		$this->assertSame($current, $this->app->site());
	}

	public function testUpdateUser()
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

		State::updateUser(
			method: 'changeEmail',
			current: $this->app->user('admin'),
			next: $next
		);

		$this->assertSame($this->app->user('admin'), $next);
	}

	public function testUpdateUserWithFalseAsMethod()
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

		State::updateUser(
			method: false,
			current: $current,
			next: $next
		);

		$this->assertSame($current, $this->app->user('admin'));
	}
}
