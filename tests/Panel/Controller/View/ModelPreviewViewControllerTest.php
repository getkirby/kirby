<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelPreviewViewController::class)]
class ModelPreviewViewControllerTest extends TestCase
{
	public function testRedirect(): void
	{
		$redirect = ModelPreviewViewController::redirect('latest');
		$this->assertSame(null, $redirect);

		new App([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			],
			'request' => [
				'query' => [
					'redirect' => 'https://getkirby.com/notes/page:2?foo=bar&_preview=true'
				],
			]
		]);

		$redirect = ModelPreviewViewController::redirect('latest');
		$this->assertSame('/panel/pages/notes/preview/latest?_query=foo%3Dbar&_params=page%3A2', $redirect);
	}

	public function testSrc(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					['slug' => 'notes']
				]
			]
		]);

		$app->impersonate('kirby');

		$model = $app->site()->page('notes');
		$token = $model->version('changes')->previewToken();
		$src   = ModelPreviewViewController::src($model);

		$this->assertSame('/notes?_preview=true', $src['latest']);
		$this->assertSame('/notes?_token=' . $token . '&_version=changes&_preview=true', $src['changes']);
	}
}
