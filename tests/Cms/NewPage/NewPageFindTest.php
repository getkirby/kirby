<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageFindTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageFindTest';

	public function testFindInMultiLanguageMode()
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

	public function testFindInSingleLanguageMode()
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
