<?php

namespace Kirby\Api\Controller;

use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\Exception\PermissionException;
use Kirby\TestCase;

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
					'template' => 'article',
					'blueprint' => [
						'fields' => [
							// we need the text field to correctly test
							// data that can be submitted and data that is
							// only passed through
							'text' => [
								'type' => 'text'
							]
						]
					]
				]
			]
		]);

		$this->page = $this->app->page('article');
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
	}

	public function testDiscard(): void
	{
		$this->app->impersonate('kirby');

		Data::write($file = $this->page->root() . '/_changes/article.txt', []);

		$response = Changes::discard($this->page);

		$this->assertSame(['status' => 'ok'], $response);

		$this->assertFileDoesNotExist($file);
	}

	public function testDiscardWithoutPermissions(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to discard this version');

		Changes::discard($this->page);
	}

	public function testPublish(): void
	{
		$this->app->impersonate('kirby');

		Data::write($this->page->root() . '/article.txt', [
			// title and uuid should be passed through
			'title' => 'Test',
			'uuid'  => 'test'
		]);

		// create a changes file to be able to check if it
		// is being cleaned up correctly after publishing
		Data::write($file = $this->page->root() . '/_changes/article.txt', [
			'title' => 'Title modified',
			'uuid'  => 'test',
		]);

		$response = Changes::publish($this->page, [
			'text' => 'Test'
		]);

		$this->assertSame(['status' => 'ok'], $response);

		// the changes should be gone now
		$this->assertFileDoesNotExist($file);

		// and the content file should be updated with the input
		$published = Data::read($this->page->root() . '/article.txt');

		$this->assertSame([
			'title' => 'Title modified',
			'text'  => 'Test',
			'uuid'  => 'test'
		], $published);
	}

	public function testPublishWithoutPermissions(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to publish this version');

		Changes::publish($this->page, []);
	}

	public function testSave(): void
	{
		$this->app->impersonate('kirby');

		Data::write($this->page->root() . '/article.txt', [
			// title and uuid should be passed through
			'title' => 'Test',
			'uuid'  => 'test'
		]);

		$response = Changes::save($this->page, [
			'text' => 'Test'
		]);

		$this->assertSame(['status' => 'ok'], $response);

		// the content file should be untouched
		$published = Data::read($this->page->root() . '/article.txt');

		$this->assertSame([
			'title' => 'Test',
			'uuid'  => 'test'
		], $published);

		// the changes file should have the changes
		$changes = Data::read($this->page->root() . '/_changes/article.txt');

		$this->assertSame([
			'title' => 'Test',
			'text'  => 'Test',
			'uuid'  => 'test',
			'lock'  => 'kirby'
		], $changes);
	}

	public function testSaveWithNoDiff(): void
	{
		$this->app->impersonate('kirby');

		Data::write($this->page->root() . '/article.txt', [
			// title and uuid should be passed through
			'title' => 'Test',
			'text'  => 'Test',
			'uuid'  => 'test'
		]);

		$response = Changes::save($this->page, [
			'text' => 'Foo'
		]);

		$this->assertSame(['status' => 'ok'], $response);
		$this->assertFileExists($this->page->root() . '/_changes/article.txt');

		$response = Changes::save($this->page, [
			'text' => 'Test'
		]);

		$this->assertSame(['status' => 'ok'], $response);
		$this->assertFileDoesNotExist($this->page->root() . '/_changes/article.txt');
	}

	/**
	 * @todo We want to ignore undefined fields later in v6. This needs to be
	 * refactored at that point to make sure that undefined fields are not saved.
	 */
	public function testSaveWithUndefinedField(): void
	{
		$this->app->impersonate('kirby');

		Data::write($this->page->root() . '/article.txt', [
			// title and uuid should be passed through
			'title' => 'Test',
			'uuid'  => 'test'
		]);

		$response = Changes::save($this->page, [
			'text'      => 'Test',
			'undefined' => 'This should be passed through'
		]);

		// the changes file should have the changes
		$changes = Data::read($this->page->root() . '/_changes/article.txt');

		$this->assertSame([
			'title'     => 'Test',
			'text'      => 'Test',
			'uuid'      => 'test',
			'undefined' => 'This should be passed through',
			'lock'      => 'kirby'
		], $changes);
	}

	public function testSaveWithoutPermissions(): void
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to change this version');

		Changes::save($this->page, []);
	}
}
