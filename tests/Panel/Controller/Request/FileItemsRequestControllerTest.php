<?php

namespace Kirby\Panel\Controller\Request;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileItemsRequestController::class)]
#[CoversClass(ModelItemsRequestController::class)]
class FileItemsRequestControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.Request.FileItemsRequestController';

	public function setUp(): void
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

	public function tearDown(): void
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

		$controller = new FileItemsRequestController();
		$data       = $controller->load();
		$this->assertSame('test.jpg', $data['items'][0]['id']);
		$this->assertNull($data['items'][1]);
		$this->assertSame('test.pdf', $data['items'][2]['id']);
	}
}
