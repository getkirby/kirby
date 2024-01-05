<?php

namespace Kirby;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public function assertIsSite($input)
	{
		$this->assertInstanceOf(Site::class, $input);
	}

	public function assertIsPage($input, string|Page $id = null)
	{
		$this->assertInstanceOf(Page::class, $input);

		if (is_string($id) === true) {
			$this->assertSame($id, $input->id());
		}

		if ($id instanceof Page) {
			$this->assertSame($input, $id);
		}
	}

	public function assertIsFile($input, string|File $id = null)
	{
		$this->assertInstanceOf(File::class, $input);

		if (is_string($id) === true) {
			$this->assertSame($id, $input->id());
		}

		if ($id instanceof File) {
			$this->assertSame($input, $id);
		}
	}

	/**
	 * Checks if the test class extending this test case class
	 * has defined a temporary directory
	 */
	protected function hasTmp(): bool
	{
		return defined(get_class($this) . '::TMP');
	}
}
