<?php

namespace Kirby\Api\Controller;

use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Changes::class)]
class ChangesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Api.Controller.Changes';
	public Page $page;

	public function setUp(): void
	{
		$this->setUpTmp();
		$this->setUpSingleLanguage(site: [
			'children' => [
				[
					'slug'     => 'article',
					'template' => 'article'
				]
			]
		]);

		$this->page = $this->app->page('article');
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
	}

	public function testDiscard()
	{
		Data::write($file = $this->page->root() . '/_changes/article.txt', []);

		$response = Changes::discard($this->page);

		$this->assertSame(['status' => 'ok'], $response);

		$this->assertFileDoesNotExist($file);
	}

	public function testPublish()
	{
		$this->app->impersonate('kirby');

		Data::write($this->page->root() . '/article.txt', []);
		Data::write($file = $this->page->root() . '/_changes/article.txt', []);

		$response = Changes::publish($this->page, [
			'title' => 'Test'
		]);

		$this->assertSame(['status' => 'ok'], $response);

		// the changes should be gone now
		$this->assertFileDoesNotExist($file);

		// and the content file should be updated with the input
		$published = Data::read($this->page->root() . '/article.txt');

		$this->assertSame(['title' => 'Test'], $published);
	}

	public function testSave()
	{
		Data::write($this->page->root() . '/article.txt', []);
		Data::write($this->page->root() . '/_changes/article.txt', []);

		$response = Changes::save($this->page, [
			'title' => 'Test'
		]);

		$this->assertSame(['status' => 'ok'], $response);

		// the content file should be untouched
		$published = Data::read($this->page->root() . '/article.txt');

		$this->assertSame([], $published);

		// the changes file should have the changes
		$changes = Data::read($this->page->root() . '/_changes/article.txt');

		$this->assertSame(['title' => 'Test'], $changes);
	}

	public function testSaveWithNoDiff()
	{
		Data::write($this->page->root() . '/article.txt', [
			'title' => 'Test'
		]);
		Data::write($this->page->root() . '/_changes/article.txt', [
			'title' => 'Test'
		]);

		$response = Changes::save($this->page, [
			'title' => 'Foo'
		]);

		$this->assertSame(['status' => 'ok'], $response);
		$this->assertFileExists($this->page->root() . '/_changes/article.txt');

		$response = Changes::save($this->page, [
			'title' => 'Test'
		]);

		$this->assertSame(['status' => 'ok'], $response);
		$this->assertFileDoesNotExist($this->page->root() . '/_changes/article.txt');
	}
}
