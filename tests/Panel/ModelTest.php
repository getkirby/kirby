<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File as ModelFile;
use Kirby\Cms\Page as ModelPage;
use Kirby\Cms\Site as ModelSite;
use Kirby\Filesystem\Asset;
use Kirby\Panel\Controller\View\ModelViewController;
use Kirby\Panel\Controller\View\PageViewController;
use PHPUnit\Framework\Attributes\CoversClass;

class CustomPanelModel extends Model
{
	public function path(): string
	{
		return 'custom';
	}

	protected function viewController(): ModelViewController
	{
		$page = new ModelPage(['slug' => 'test']);
		return new PageViewController($page);
	}
}

class ModelSiteWithImageMethod extends ModelSite
{
	public function panelBack()
	{
		return 'blue';
	}

	public function cover()
	{
		return new Asset('tmp/test.svg');
	}
}

#[CoversClass(Model::class)]
class ModelTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Model';

	protected function panel(array $props = [])
	{
		$site = new ModelSite($props);
		return new CustomPanelModel($site);
	}

	public function testDragTextFromCallbackMarkdown(): void
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'markdown' => [
						'fileDragText' => function (ModelFile $file, string $url) {
							if ($file->extension() === 'heic') {
								return sprintf('![](%s)', $file->id());
							}

							return null;
						},
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.heic'],
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		// Custom function does not match and returns null, default case
		$file  = $app->page('test')->file('test.jpg');
		$panel = new CustomPanelModel($file);
		$this->assertNull($panel->dragTextFromCallback('markdown', $file, $file->filename()));

		// Custom function should return image tag for heic
		$file  = $app->page('test')->file('test.heic');
		$panel = new CustomPanelModel($file);
		$this->assertSame('![](test/test.heic)', $panel->dragTextFromCallback('markdown', $file, $file->filename()));
	}

	public function testDragTextFromCallbackKirbytext(): void
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => [
					'kirbytext' => [
						'fileDragText' => function (ModelFile $file, string $url) {
							if ($file->extension() === 'heic') {
								return sprintf('(image: %s)', $file->id());
							}

							return null;
						},
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.heic'],
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		// Custom function does not match and returns null, default case
		$file  = $app->page('test')->file('test.jpg');
		$panel = new CustomPanelModel($file);
		$this->assertNull($panel->dragTextFromCallback('kirbytext', $file, $file->filename()));

		// Custom function should return image tag for heic
		$file  = $app->page('test')->file('test.heic');
		$panel = new CustomPanelModel($file);
		$this->assertSame('(image: test/test.heic)', $panel->dragTextFromCallback('kirbytext', $file, $file->filename()));
	}

	public function testDragTextType(): void
	{
		$panel = $this->panel();

		// with passed value
		$this->assertSame('markdown', $panel->dragTextType('markdown'));
		$this->assertSame('kirbytext', $panel->dragTextType('kirbytext'));
		$this->assertSame('kirbytext', $panel->dragTextType('foo'));

		// auto
		$this->assertSame('kirbytext', $panel->dragTextType());

		$app = $this->app->clone([
			'options' => ['panel' => ['kirbytext' => false]]
		]);
		$this->assertSame('markdown', $panel->dragTextType());

		// reset app instance
		new App();
	}

	public function testDropdownOption(): void
	{
		$model  = new CustomPanelModel(new ModelSite());
		$option = $model->dropdownOption();
		$expected = [
			'icon' => 'page',
			'image' => [
				'back'  => 'black',
				'color' => 'gray-500',
				'cover' => false,
				'icon'  => 'page'
			],
			'link' => '/custom',
			'text' => null
		];

		$this->assertSame($expected, $option);
	}

	public function testImage(): void
	{
		$panel = $this->panel([
			'files' => [
				['filename' => 'test.jpg']
			]
		]);

		// defaults
		$image = $panel->image();
		$this->assertArrayHasKey('back', $image);
		$this->assertArrayHasKey('cover', $image);
		$this->assertArrayHasKey('icon', $image);
		$this->assertFalse($image['cover']);
		$this->assertSame('page', $image['icon']);

		// deactivate
		$this->assertNull($panel->image(false));

		// icon
		$image = $panel->image('icon');
		$this->assertSame('page', $image['icon']);
		$this->assertArrayNotHasKey('src', $image);
		$this->assertArrayNotHasKey('url', $image);

		// invalid query
		$image = $panel->image('site.foo');
		$this->assertArrayNotHasKey('url', $image);
		$this->assertArrayNotHasKey('query', $image);

		// valid query
		$image = $panel->image('site.image');
		$this->assertArrayHasKey('url', $image);
		$this->assertArrayHasKey('src', $image);
		$this->assertArrayHasKey('srcset', $image);
		$this->assertArrayNotHasKey('query', $image);
		$this->assertStringContainsString('test-38x.jpg 38w', $image['srcset']);
		$this->assertStringContainsString('test-76x.jpg 76w', $image['srcset']);

		// cards
		$image = $panel->image('site.image', 'cards');
		$this->assertStringContainsString('test-352x.jpg 352w', $image['srcset']);
		$this->assertStringContainsString('test-864x.jpg 864w', $image['srcset']);
		$this->assertStringContainsString('test-1408x.jpg 1408w', $image['srcset']);

		// cardlets
		$image = $panel->image('site.image', 'cardlets');
		$this->assertStringContainsString('test-96x.jpg 96w', $image['srcset']);
		$this->assertStringContainsString('test-192x.jpg 192w', $image['srcset']);

		// table
		$image = $panel->image('site.image', 'table');
		$this->assertStringContainsString('test-38x.jpg 38w', $image['srcset']);
		$this->assertStringContainsString('test-76x.jpg 76w', $image['srcset']);

		// full options
		$image = $panel->image([
			'cover' => true,
			'icon'  => $icon = 'heart',
			'query' => 'site.image',
			'ratio' => $ratio = '16/9'
		]);
		$this->assertArrayHasKey('url', $image);
		$this->assertArrayHasKey('src', $image);
		$this->assertArrayHasKey('srcset', $image);
		$this->assertSame($icon, $image['icon']);
		$this->assertSame($ratio, $image['ratio']);
		$this->assertStringContainsString('test-38x38-crop.jpg 1x', $image['srcset']);
		$this->assertStringContainsString('test-76x76-crop.jpg 2x', $image['srcset']);

		// string ratio
		$image = $panel->image([
			'cover' => true,
			'query' => 'site.image',
			'ratio' => $ratio = '3/2'
		], 'cards');
		$this->assertArrayHasKey('url', $image);
		$this->assertArrayHasKey('src', $image);
		$this->assertArrayHasKey('srcset', $image);
		$this->assertSame($ratio, $image['ratio']);
		$this->assertStringContainsString('test-352x235-crop.jpg 352w', $image['srcset']);
		$this->assertStringContainsString('test-864x576-crop.jpg 864w', $image['srcset']);
		$this->assertStringContainsString('test-1408x939-crop.jpg 1408w', $image['srcset']);

		// numeric ratio
		$image = $panel->image([
			'cover' => true,
			'query' => 'site.image',
			'ratio' => $ratio = 1.5
		], 'cards');
		$this->assertArrayHasKey('url', $image);
		$this->assertArrayHasKey('src', $image);
		$this->assertArrayHasKey('srcset', $image);
		$this->assertSame($ratio, $image['ratio']);
		$this->assertStringContainsString('test-352x235-crop.jpg 352w', $image['srcset']);
		$this->assertStringContainsString('test-864x576-crop.jpg 864w', $image['srcset']);
		$this->assertStringContainsString('test-1408x939-crop.jpg 1408w', $image['srcset']);
	}

	public function testImageWithNonResizableAsset(): void
	{
		$site  = new ModelSiteWithImageMethod([]);
		$panel = new CustomPanelModel($site);
		$image = $panel->image('site.cover');
		$this->assertSame('/tmp/test.svg', $image['url']);
		$this->assertSame('/tmp/test.svg', $image['src']);
	}

	public function testImageWithBlueprint(): void
	{
		$app  = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'icon' => 'heart',
					'image'  => [
						'back' => 'red',
						'ratio' => '1/2'
					]
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'template' => 'test'
					]
				]
			]
		]);

		$panel = $app->page('test')->panel();
		$image = $panel->image(['ratio' => '1/1', 'color' => 'yellow']);
		$this->assertSame('red', $image['back']);
		$this->assertSame('yellow', $image['color']);
		$this->assertSame('heart', $image['icon']);
		$this->assertSame('1/2', $image['ratio']);
		$this->assertArrayNotHasKey('query', $image);
		$this->assertArrayNotHasKey('url', $image);
	}

	public function testImageWithBlueprintFalse(): void
	{
		$app  = $this->app->clone([
			'blueprints' => [
				'pages/foo' => [
					'image' => false
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'template' => 'foo'
					]
				]
			]
		]);

		$panel = $app->page('test')->panel();
		$image = $panel->image(['icon' => 'heart']);
		$this->assertSame('heart', $image['icon']);

		$image = $panel->image([]);
		$this->assertNull($image);

		$image = $panel->image();
		$this->assertNull($image);
	}

	public function testImageWithBlueprintString(): void
	{
		$app  = $this->app->clone([
			'blueprints' => [
				'pages/fox' => [
					'image' => 'site.page("test").image'
				]
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'template' => 'fox',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		$panel = $app->page('test')->panel();
		$image = $panel->image([]);
		$this->assertStringEndsWith('test.jpg', $image['url']);
	}

	public function testImageWithQuery(): void
	{
		$site  = new ModelSiteWithImageMethod();
		$panel = new CustomPanelModel($site);
		$image = $panel->image(['back' => '{{ site.panelBack }}']);
		$this->assertSame('blue', $image['back']);
	}

	public function testImagePlaceholder(): void
	{
		$this->assertIsString(Model::imagePlaceholder());
		$this->assertStringStartsWith('data:image/gif;base64,', Model::imagePlaceholder());
	}

	public function testModel(): void
	{
		$panel  = $this->panel();
		$this->assertInstanceOf(ModelSite::class, $panel->model());
	}

	public function testPickerData(): void
	{
		$panel = $this->panel();
		$data = $panel->pickerData();

		$this->assertSame([
			'image' => [
				'back' => 'pattern',
				'color' => 'gray-500',
				'cover' => false,
				'icon' => 'page',
			],
			'info' => '',
			'layout' => 'list',
			'text' => '',
			'id' => null,
			'link' => '/site',
			'permissions' => [
				'changeTitle' => false,
				'update' => false,
			],
			'uuid' => 'site://',
			'sortable' => true,
			'url' => '/custom',
		], $data);
	}

	public function testToLink(): void
	{
		$panel = $this->panel([
			'content' => [
				'title'  => $title = 'Kirby Kirby Kirby',
				'author' => $author = 'Bastian Allgeier'
			]
		]);

		$toLink = $panel->toLink();
		$this->assertSame('/custom', $toLink['link']);
		$this->assertSame($title, $toLink['title']);

		$toLink = $panel->toLink('author');
		$this->assertSame('/custom', $toLink['link']);
		$this->assertSame($author, $toLink['title']);
	}

	public function testUrl(): void
	{
		$this->assertSame('/panel/custom', $this->panel()->url());
		$this->assertSame('/custom', $this->panel()->url(true));
	}
}
