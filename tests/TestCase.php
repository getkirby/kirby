<?php

namespace Kirby;

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public function assertIsFile($input, string|File $id = null): void
	{
		$this->assertInstanceOf(File::class, $input);

		if (is_string($id) === true) {
			$this->assertSame($id, $input->id());
		}

		if ($id instanceof File) {
			$this->assertSame($input, $id);
		}
	}

	public function assertIsPage($input, string|Page $id = null): void
	{
		$this->assertInstanceOf(Page::class, $input);

		if (is_string($id) === true) {
			$this->assertSame($id, $input->id());
		}

		if ($id instanceof Page) {
			$this->assertSame($input, $id);
		}
	}

	public function assertIsUser($input, string|User $id = null): void
	{
		$this->assertInstanceOf(User::class, $input);

		if (is_string($id) === true) {
			$this->assertSame($id, $input->id());
		}

		if ($id instanceof User) {
			$this->assertSame($input, $id);
		}
	}

	public function assertIsSite($input): void
	{
		$this->assertInstanceOf(Site::class, $input);
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
