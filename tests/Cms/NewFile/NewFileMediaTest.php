<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use Kirby\Cms\NewPage as Page;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileMediaTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileMedia';

	public function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'options' => [
				'content.salt' => 'test'
			]
		]);
	}

	public function testMediaHash(): void
	{
		F::write($file = static::TMP . '/content/test.jpg', 'test');
		touch($file, 5432112345);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame('08756f3115-5432112345', $file->mediaHash());
	}

	public function testMediaToken(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame('08756f3115', $file->mediaToken());
	}
}
