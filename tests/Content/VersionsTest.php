<?php

namespace Kirby\Content;

use Kirby\Cms\Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Versions::class)]
class VersionsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Content.Versions';

	public function testDeleteMultiLanguage(): void
	{
		$this->setUpMultiLanguage();

		$model = new Page(['slug' => 'test']);

		$model->version('latest')->save(['title' => 'original EN'], 'en');
		$model->version('latest')->save(['title' => 'original DE'], 'de');
		$model->version('changes')->save(['title' => 'modified EN'], 'en');
		$model->version('changes')->save(['title' => 'modified DE'], 'de');

		$this->assertTrue($model->version('latest')->exists('en'));
		$this->assertTrue($model->version('latest')->exists('de'));
		$this->assertTrue($model->version('changes')->exists('en'));
		$this->assertTrue($model->version('changes')->exists('de'));

		$model->versions()->delete();

		$this->assertFalse($model->version('latest')->exists('en'));
		$this->assertFalse($model->version('latest')->exists('de'));
		$this->assertFalse($model->version('changes')->exists('en'));
		$this->assertFalse($model->version('changes')->exists('de'));
	}

	public function testDeleteSingleLanguage(): void
	{
		$this->setUpSingleLanguage();

		$model = new Page(['slug' => 'test']);

		$model->version('latest')->save(['title' => 'original']);
		$model->version('changes')->save(['title' => 'modified']);

		$this->assertTrue($model->version('latest')->exists());
		$this->assertTrue($model->version('changes')->exists());

		$model->versions()->delete();

		$this->assertFalse($model->version('latest')->exists());
		$this->assertFalse($model->version('changes')->exists());
	}

	public function testLoad(): void
	{
		$model    = new Page(['slug' => 'test']);
		$versions = Versions::load($model);

		$this->assertCount(2, $versions);
		$this->assertSame('changes', (string)$versions->first()->id());
		$this->assertSame('latest', (string)$versions->last()->id());
	}
}
