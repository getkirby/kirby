<?php

namespace Kirby\Panel\Lab;

use Kirby\Data\Data;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Docs::class)]
class DocsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Lab.Docs';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'roots' => [
				'panel' => static::TMP . '/panel'
			]
		]);

		Data::write(static::TMP . '/panel/dist/ui/Box.json', ['sourceFile' => 'dist/Box.vue']);
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

	public function testAll(): void
	{
		// leftover dev server docs are ignored
		$this->assertSame(['k-box'], array_column(Docs::all(), 'text'));

		$this->setDevMode();
		$this->assertSame(['k-box', 'k-draft'], array_column(Docs::all(), 'text'));
	}

	public function testIsDev(): void
	{
		$this->assertFalse(Docs::isDev());

		// a running dev server alone is not enough
		touch(static::TMP . '/panel/.vite-running');
		$this->assertFalse(Docs::isDev());

		$this->setDevMode();
		$this->assertTrue(Docs::isDev());
	}
}
