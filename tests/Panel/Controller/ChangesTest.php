<?php

namespace Kirby\Panel\Controller;

use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\TestCase;

class ChangesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.Changes';
	public Page $page;

	public function setUp(): void
	{
		$this->setUpTmp();
		$this->setUpSingleLanguage();
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
	}

	public function testDiscard()
	{
		Data::write($file = $this->model->root() . '/_changes/article.txt', []);

		$response = Changes::discard($this->model);

		$this->assertSame(['status' => 'ok'], $response);

		$this->assertFileDoesNotExist($file);
	}

	public function testPublish()
	{
		Data::write($this->model->root() . '/article.txt', []);
		Data::write($file = $this->model->root() . '/_changes/article.txt', []);

		$response = Changes::publish($this->model, [
			'title' => 'Test'
		]);

		$this->assertSame(['status' => 'ok'], $response);

		// the changes should be gone now
		$this->assertFileDoesNotExist($file);

		// and the content file should be updated with the input
		$published = Data::read($this->model->root() . '/article.txt');

		$this->assertSame(['title' => 'Test'], $published);
	}

	public function testSave()
	{
		Data::write($this->model->root() . '/article.txt', []);
		Data::write($this->model->root() . '/_changes/article.txt', []);

		$response = Changes::save($this->model, [
			'title' => 'Test'
		]);

		$this->assertSame(['status' => 'ok'], $response);

		// the content file should be untouched
		$published = Data::read($this->model->root() . '/article.txt');

		$this->assertSame([], $published);

		// the changes file should have the changes
		$changes = Data::read($this->model->root() . '/_changes/article.txt');

		$this->assertSame(['title' => 'Test'], $changes);
	}
}
