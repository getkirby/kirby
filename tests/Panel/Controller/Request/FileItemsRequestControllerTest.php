<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\App;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileItemsRequestController::class)]
#[CoversClass(ModelItemsRequestController::class)]
class FileItemsRequestControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.FileItemsRequestController';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'files' => [
					[
						'filename' => 'test.pdf',
						'content'  => ['uuid' => 'pdf']
					],
					[
						'filename' => 'test.jpg',
						'content'  => ['uuid' => 'jpg']
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
	}

	protected function tearDown(): void
	{
		App::destroy();
	}

	public function testLoad(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'items' => 'file://jpg,file://foo,file://pdf'
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new FileItemsRequestController();
		$data       = $controller->load();
		$this->assertSame('test.jpg', $data['items'][0]['id']);
		$this->assertNull($data['items'][1]);
		$this->assertSame('test.pdf', $data['items'][2]['id']);
	}

	public function testLoadAppliesImageHints(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'items'  => 'file://jpg',
					'layout' => 'cards',
					'image'  => json_encode(['ratio' => '16/9', 'cover' => true])
				],
			]
		]);

		$this->app->impersonate('kirby');

		// copy a real image so the file is resizable and a srcset is built
		$file = $this->app->file('file://jpg');
		F::copy(__DIR__ . '/../../fixtures/image/test.jpg', $file->root());

		$controller = new FileItemsRequestController();
		$item       = $controller->load()['items'][0];

		// the literal ratio and cover hint pass through to the thumbnail,
		// driving a cropped srcset for the requested `cards` layout
		$this->assertSame('16/9', $item['image']['ratio']);
		$this->assertTrue($item['image']['cover']);
		$this->assertMatchesRegularExpression('/test-128x72-crop0-28-128-72-q85-[0-9a-f]{10}\.jpg 400w/', $item['image']['srcset']);
	}

	public function testLoadDoesNotEvaluateRequestTemplates(): void
	{
		$this->app = $this->app->clone([
			'options' => ['secret' => 'TOP-SECRET'],
			'request' => [
				'query' => [
					'items' => 'file://jpg',
					'text'  => '{{ kirby.option("secret") }}',
					'info'  => '{{ kirby.option("secret") }}',
					'image' => json_encode([
						'query' => 'kirby.option("secret")',
						'ratio' => '{{ kirby.option("secret") }}'
					])
				],
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new FileItemsRequestController();
		$item       = $controller->load()['items'][0];

		// the request templates are ignored: the item renders
		// with its own defaults, never the injected query
		$this->assertSame('test.jpg', (string)$item['text']);
		$this->assertStringNotContainsString('TOP-SECRET', (string)$item['text']);
		$this->assertStringNotContainsString('TOP-SECRET', (string)$item['info']);
		$this->assertArrayNotHasKey('ratio', $item['image']);
	}

	public function testLoadNotListable(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			],
			'blueprints' => [
				'files/default' => [
					'options' => ['list' => false]
				]
			],
			'request' => [
				'query' => [
					'items' => 'file://jpg,file://pdf'
				],
			]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$controller = new FileItemsRequestController();
		$data       = $controller->load();
		$this->assertNull($data['items'][0]);
		$this->assertNull($data['items'][1]);
	}
}
