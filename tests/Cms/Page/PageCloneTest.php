<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageCloneTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageClone';

	public function testClone(): void
	{
		$original = new Page([
			'slug' => 'test'
		]);

		$clone = $original->clone();

		$this->assertNotSame($original, $clone);
		$this->assertSame($original->slug(), $clone->slug());
		$this->assertSame($original->content()->toArray(), $clone->content()->toArray());
		$this->assertNotSame($original->storage(), $clone->storage());
		$this->assertInstanceOf($original->storage()::class, $clone->storage());
	}

	public function testCloneWithVirtualContent(): void
	{
		$original = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Test Title',
				'subtitle' => 'Test Subtitle'
			]
		]);

		$clone = $original->clone();

		$this->assertSame($original->content()->toArray(), $clone->content()->toArray());
	}

	public function testCloneWithVirtualTranslations(): void
	{
		$this->setUpMultiLanguage();

		$original = new Page([
			'slug' => 'test',
			'translations' => [
				[
					'code' => 'en',
					'content' => $contentEN = [
						'title' => 'Test Title',
					]
				],
				[
					'code' => 'de',
					'content' => $contentDE = [
						'title' => 'Test Title DE',
					]
				]
			]
		]);

		$clone = $original->clone();

		$this->assertSame($original->content('en')->toArray(), $clone->content('en')->toArray());
		$this->assertSame($original->content('de')->toArray(), $clone->content('de')->toArray());
	}

	public function testCloneWithContentOnDisk(): void
	{
		$original = new Page([
			'slug' => 'test'
		]);

		Data::write(static::TMP . '/content/test/default.txt', $content = [
			'title'    => 'Title',
			'subtitle' => 'Subtitle'
		]);

		$clone = $original->clone();

		$this->assertSame($content, $clone->content()->toArray());
		$this->assertSame($original->content()->toArray(), $clone->content()->toArray());
	}

	public function testCloneWithTranslationsOnDisk(): void
	{
		$this->setUpMultiLanguage();

		$original = new Page([
			'slug' => 'test',
		]);

		Data::write(static::TMP . '/content/test/default.en.txt', $contentEN = [
			'title'    => 'Title EN',
			'subtitle' => 'Subtitle EN'
		]);

		Data::write(static::TMP . '/content/test/default.de.txt', $contentDE = [
			'title'    => 'Title DE',
			'subtitle' => 'Subtitle DE'
		]);

		$clone = $original->clone();

		$this->assertSame($contentEN, $clone->content('en')->toArray());
		$this->assertSame($contentDE, $clone->content('de')->toArray());
		$this->assertSame($original->content('en')->toArray(), $clone->content('en')->toArray());
		$this->assertSame($original->content('de')->toArray(), $clone->content('de')->toArray());
	}
}
