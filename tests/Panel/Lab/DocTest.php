<?php

namespace Kirby\Panel\Lab;

use Kirby\Data\Data;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Doc::class)]
class DocTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Lab.Doc';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roots' => [
				'panel' => static::TMP . '/panel'
			]
		]);

		Data::write(static::TMP . '/panel/dist/ui/Box.json', ['sourceFile' => 'dist/Box.vue']);
		Data::write(static::TMP . '/panel/dist/ui/Button.json', ['sourceFile' => 'dist/Button.vue']);
		Data::write(static::TMP . '/panel/tmp/Box.json', ['sourceFile' => 'tmp/Box.vue']);
		Data::write(static::TMP . '/panel/tmp/Draft.json', ['sourceFile' => 'tmp/Draft.vue']);
	}

	public function setDevMode(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'dev' => true
				]
			]
		]);

		touch(static::TMP . '/panel/.vite-running');
	}

	public function testExists(): void
	{
		$this->assertTrue(Doc::exists('k-box'));
		$this->assertFalse(Doc::exists('k-draft'));
		$this->assertFalse(Doc::exists('k-missing'));
	}

	public function testExistsInDevMode(): void
	{
		$this->setDevMode();
		$this->assertTrue(Doc::exists('k-box'));
		$this->assertTrue(Doc::exists('k-draft'));
		$this->assertFalse(Doc::exists('k-missing'));
	}

	public function testFactory(): void
	{
		// leftover dev server docs are ignored
		$this->assertSame('dist/Box.vue', Doc::factory('k-box')->source);
		$this->assertNull(Doc::factory('k-draft'));
	}

	public function testFactoryInDevMode(): void
	{
		$this->setDevMode();
		$this->assertSame('tmp/Box.vue', Doc::factory('k-box')->source);
		$this->assertSame('dist/Button.vue', Doc::factory('k-button')->source);
		$this->assertSame('tmp/Draft.vue', Doc::factory('k-draft')->source);
	}
}
