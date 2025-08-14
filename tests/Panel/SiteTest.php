<?php

namespace Kirby\Panel;

use Kirby\Cms\Site as ModelSite;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Kirby\Panel\Site::class)]
#[CoversClass(Model::class)]
class SiteTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Site';

	protected function panel(array $props = [])
	{
		$site = new ModelSite($props);
		return new Site($site);
	}

	public function testDropdownOption(): void
	{
		$model = $this->panel([
			'content' => [
				'title' => 'Test site'
			]
		]);

		$option = $model->dropdownOption();

		$this->assertSame('home', $option['icon']);
		$this->assertSame('Test site', $option['text']);
		$this->assertSame('/site', $option['link']);
	}

	public function testImage(): void
	{
		$panel = $this->panel([
			'files' => [
				['filename' => 'test.jpg']
			]
		]);

		// fallback to model itself
		$image = $panel->image();
		$this->assertTrue(Str::endsWith($image['url'], '/test.jpg'));
	}

	public function testImageCover(): void
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
				]
			]
		]);

		$site  = $app->site();
		$panel = new Site($site);

		$hash = $site->image()->mediaHash();
		$mediaUrl = $site->mediaUrl() . '/' . $hash;

		// cover disabled as default
		$this->assertSame([
			'back' => 'pattern',
			'color' => 'gray-500',
			'cover' => false,
			'icon' => 'page',
			'url' => $mediaUrl . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
		], $panel->image());

		// cover enabled
		$this->assertSame([
			'back' => 'pattern',
			'color' => 'gray-500',
			'cover' => true,
			'icon' => 'page',
			'url' => $mediaUrl . '/test.jpg',
			'src' => Model::imagePlaceholder(),
			'srcset' => $mediaUrl . '/test-38x38-crop.jpg 1x, ' . $mediaUrl . '/test-76x76-crop.jpg 2x'
		], $panel->image(['cover' => true]));
	}

	public function testPath(): void
	{
		$this->assertSame('site', $this->panel()->path());
	}

	public function testPreviewPermissionsWithoutHomePage(): void
	{
		$props = $this->panel()->props();

		$this->assertFalse($props['permissions']['preview']);
	}

	public function testPreviewPermissionsWithHomePage(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		// without logged in user
		$props = $this->app->site()->panel()->props();

		$this->assertFalse($props['permissions']['preview']);

		// with logged in user
		$this->app->impersonate('kirby');

		$props = $this->app->site()->panel()->props();

		$this->assertTrue($props['permissions']['preview']);
	}
}
