<?php

namespace Kirby\Email;

use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Email\Attachment
 * @covers ::__construct
 */
class AttachmentTest extends TestCase
{
	/**
	 * @covers ::factory
	 */
	public function testFactory(): void
	{
		$root = __DIR__ . '/fixtures/files/test.jpg';

		$attachment = Attachment::factory($root);
		$this->assertSame($root, $attachment->root());

		$attachments = Attachment::factory([$root]);
		$this->assertSame($root, $attachments[0]->root());


		$file = new File([
			'filename' => 'test.jpg',
			'parent'  => new Page(['slug' => 'test'])
		]);
		$attachment = Attachment::factory($file);
		$this->assertSame('/dev/null/content/test/test.jpg', $attachment->root());

		$files = new Files([
			new File([
				'filename' => 'test.jpg',
				'parent'   => new Page(['slug' => 'test'])
			]),
			new File([
				'filename' => 'foo.mp4',
				'parent'   => new Page(['slug' => 'test'])
			]),
		]);

		$attachments = Attachment::factory($files);
		$this->assertSame('/dev/null/content/test/test.jpg', $attachments[0]->root());
		$this->assertSame('/dev/null/content/test/foo.mp4', $attachments[1]->root());
	}

	/**
	 * @covers ::root
	 */
	public function testRoot(): void
	{
		$attachment = new Attachment(
			root: $root = __DIR__ . '/fixtures/files/test.jpg'
		);
		$this->assertSame($root, $attachment->root());
	}
}
