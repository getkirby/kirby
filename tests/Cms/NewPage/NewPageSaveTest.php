<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageSaveTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageSave';

	public function testSaveInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$original = new Page([
			'slug' => 'test',
		]);

		$this->assertSame([], $original->content()->toArray());
		$this->assertSame([], $original->content('en')->toArray());
		$this->assertSame([], $original->content('de')->toArray());

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.en.txt');
		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.de.txt');

		$savedEN = $original->save($contentEN = [
			'title' => 'Test Title EN',
		]);

		$savedDE = $savedEN->save($contentDE = [
			'title' => 'Test Title DE',
		], 'de');

		$savedENContentOnDisk = Data::read(static::TMP . '/content/test/default.en.txt');
		$savedDEContentOnDisk = Data::read(static::TMP . '/content/test/default.de.txt');

		$this->assertNotSame($original, $savedEN);
		$this->assertSame($contentEN, $savedEN->content()->toArray());
		$this->assertSame($contentEN, $savedEN->content('en')->toArray());
		$this->assertSame($contentEN, $savedENContentOnDisk);

		$this->assertNotSame($original, $savedDE);
		$this->assertSame($contentDE, $savedDE->content('de')->toArray());
		$this->assertSame($contentDE, $savedDEContentOnDisk);
	}

	public function testSaveInSingleLanguageMode(): void
	{
		$original = new Page([
			'slug' => 'test',
		]);

		$this->assertSame([], $original->content()->toArray());

		$this->assertFileDoesNotExist(static::TMP . '/content/test/default.txt');

		$saved = $original->save($content = [
			'title' => 'Test Title',
		]);

		$savedContentOnDisk = Data::read(static::TMP . '/content/test/default.txt');

		$this->assertNotSame($original, $saved);
		$this->assertSame($content, $saved->content()->toArray());
		$this->assertSame($content, $savedContentOnDisk);
	}
}
