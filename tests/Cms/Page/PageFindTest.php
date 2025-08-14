<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageFindTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageFind';

	public function testFindInMultiLanguageMode(): void
	{
		$this->setupMultiLanguage();

		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'grandma',
						'translations' => [
							[
								'code' => 'de',
								'slug' => 'oma',
							]
						],
						'children' => [
							[
								'slug' => 'mother',
								'translations' => [
									[
										'code' => 'de',
										'slug' => 'mutter',
									]
								],
								'children' => [
									[
										'slug' => 'child',
										'translations' => [
											[
												'code' => 'de',
												'slug' => 'kind',
											]
										]
									]
								]
							]
						]
					]
				]
			]
		]);

		$this->assertSame('grandma', $this->app->page('grandma')->id());
		$this->assertSame('grandma/mother', $this->app->page('grandma/mother')->id());
		$this->assertSame('grandma/mother/child', $this->app->page('grandma/mother/child')->id());

		$this->app->setCurrentLanguage('de');

		$this->assertSame('grandma', $this->app->page('oma')->id());
		$this->assertSame('grandma/mother', $this->app->page('oma/mutter')->id());
		$this->assertSame('grandma/mother/child', $this->app->page('oma/mutter/kind')->id());
	}

	public function testFindInSingleLanguageMode(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'grandma',
						'children' => [
							[
								'slug' => 'mother',
								'children' => [
									[
										'slug' => 'child',
									]
								]
							]
						]
					]
				]
			]
		]);

		$this->assertSame('grandma', $this->app->page('grandma')->id());
		$this->assertSame('grandma/mother', $this->app->page('grandma/mother')->id());
		$this->assertSame('grandma/mother/child', $this->app->page('grandma/mother/child')->id());
	}
}
