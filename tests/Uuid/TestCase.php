<?php

namespace Kirby\Uuid;

use Kirby\Cms\App;
use Kirby\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	public function setUp(): void
	{
		$this->app = $this->app();
		$this->setUpTmp();
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
		Uuids::cache()->flush();
	}

	protected function app(): App
	{
		return new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null',
			],
			'options' => [
				'url' => 'https://getkirby.com'
			],
			'site' => [
				'children' => [
					[
						'slug'    => 'page-a',
						'template' => 'note',
						'content' => [
							'uuid' => 'my-page',
							'authors' => '
-
  name: Homer Simpson
  uuid: my-struct
-
  name: Lisa Simpson
  uuid: another-struct
',
							'notes' => '[{"content":{"text":"<p>Foo bar<\/p>"},"id":"my-block-1","isHidden":false,"type":"text"},{"content":{"text":"<ul><li>one<\/li><li>two<\/li><li>three<\/li><\/ul>"},"id":"my-block-2","isHidden":false,"type":"list"}]'
						],
						'children' => [
							[
								'slug'    => 'subpage-a',
								'content' => [
									'uuid' => 'my-subpage'
								]
							]
						],
						'files' => [
							[
								'filename' => 'test.pdf',
								'content'  => ['uuid' => 'my-file']
							]
						]
					],
					[
						'slug'    => 'page-b',
						'template' => 'album',
						'content' => [
							'authors' => '
-
  name: Marge Simpson
  uuid: not-another-struct
',
							'notes' => '[{"content":{"text":"<p>Foo bar<\/p>"},"id":"my-block-3","isHidden":true,"type":"text"}]'
						],
						'files' => [
							[
								'filename' => 'foo.pdf'
							]
						]
					]
				],
				'files' => [
					[
						'filename' => 'site.txt',
						'content'  => ['uuid' => 'my-site-file']
					]
				]
			],
			'users' => [
				[
					'id' => 'my-user',
					'email' => 'homer@simpson.com',
					'files' => [
						[
							'filename' => 'user.jpg',
							'content'  => ['uuid' => 'my-avatar']
						]
					]
				]
			],
			'blueprints' => [
				'pages/album' => [
					'fields' => [
						'authors' => ['type' => 'structure'],
						'notes'   => ['type' => 'blocks'],
						'foo'     => ['type' => 'radio']
					]
				],
				'pages/note' => [
					'fields' => [
						'authors' => ['type' => 'structure'],
						'notes'   => ['type' => 'blocks'],
						'bar'     => ['type' => 'radio']
					]
				]
			],
		]);
	}
}
