<?php

namespace Kirby\Cms;

use TypeError;

class SiteFilesTest extends TestCase
{
	public function testDefaultFiles()
	{
		$site = new Site();
		$this->assertInstanceOf(Files::class, $site->files());
	}

	public function testInvalidFiles()
	{
		$this->expectException(TypeError::class);
		new Site(['files' => 'files']);
	}

	public function testFiles()
	{
		$site  = new Site([
			'files' => []
		]);

		$this->assertInstanceOf(Files::class, $site->files());
	}
}
