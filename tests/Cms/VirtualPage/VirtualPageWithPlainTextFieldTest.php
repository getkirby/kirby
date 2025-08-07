<?php

namespace Kirby\Cms;

use Kirby\Content\Content;
use Kirby\Content\Field;
use Kirby\Content\PlainTextStorage;
use Kirby\Content\VersionId;
use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversClass;

class VirtualPageWithPlainTextField extends Page
{
	public function plainTextField(): Field
	{
		$storage   = new PlainTextStorage($this);
		$language  = Language::ensure();
		$versionId = VersionId::latest();
		$content   = new Content($storage->read($versionId, $language), $this);

		return $content->plainTextField();
	}
}

#[CoversClass(Page::class)]
class VirtualPageWithPlainTextFieldTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.VirtualPageWithPlainTextField';

	public function testContent(): void
	{
		$page = new VirtualPageWithPlainTextField([
			'slug'    => 'test',
			'template' => 'test',
			'content' => [
				'title'          => 'Title (virtual)',
				'plainTextField' => 'Plain Text Field (virtual)'
			]
		]);

		Data::write(self::TMP . '/content/test/test.txt', [
			'title'          => 'Title (on disk)',
			'plainTextField' => 'Plain Text Field (on disk)'
		]);

		$this->assertSame('Title (virtual)', $page->title()->value(), 'Should come from virtual content');
		$this->assertSame('Plain Text Field (on disk)', $page->plainTextField()->value(), 'Should come from on-disk content');
	}
}
