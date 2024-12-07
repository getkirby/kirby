<?php

namespace Kirby\Api;

use Kirby\Cms\Page;

class FileModelTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileApiModel';

	public function testNextWithTemplate()
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg', 'content' => ['template' => 'test']],
				['filename' => 'b.jpg', 'content' => ['template' => 'test']],
			]
		]);

		$next = $this->attr($page->file('a.jpg'), 'nextWithTemplate');
		$this->assertSame('b.jpg', $next['filename']);
	}

	public function testPrevWithTemplate()
	{
		$page = new Page([
			'slug'  => 'test',
			'files' => [
				['filename' => 'a.jpg', 'content' => ['template' => 'test']],
				['filename' => 'b.jpg', 'content' => ['template' => 'test']],
			]
		]);

		$next = $this->attr($page->file('b.jpg'), 'prevWithTemplate');
		$this->assertSame('a.jpg', $next['filename']);
	}
}
