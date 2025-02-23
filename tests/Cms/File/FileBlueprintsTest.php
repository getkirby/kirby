<?php

namespace Kirby\Cms;



use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileBlueprintsTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileBlueprints';

	public function testBlueprints(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						[
							'type' => 'files',
							'template' => 'for-section/a'
						],
						[
							'type' => 'files',
							'template' => 'for-section/b'
						],
						[
							'type' => 'files',
							'template' => 'not-exist'
						],
						[
							'type' => 'fields',
							'fields' => [
								'a' => [
									'type' => 'info'
								],
								'b' => [
									'type' => 'files'
								],
								'c' => [
									'type'    => 'files',
									'uploads' => 'for-fields/a'
								],
								'd' => [
									'type'    => 'files',
									'uploads' => [
										'template' => 'for-fields/b'
									]
								],
								'e' => [
									'type'    => 'files',
									'uploads' => [
										'parent'   => 'foo',
										'template' => 'for-fields/c'
									]
								],
								'f' => [
									'type'    => 'files',
									'uploads' => 'for-fields/c'
								],
								'g' => [
									'type'    => 'textarea',
									'uploads' => 'for-fields/d'
								],
								'h' => [
									'type'    => 'structure',
									'fields'  => [
										[
											'type'    => 'files',
											'uploads' => 'for-fields/e'
										],
										[
											'type'    => 'structure',
											'fields'  => [
												[
													'type'    => 'files',
													'uploads' => 'for-fields/f'
												]
											]
										]
									]
								],
							]
						]
					]
				],
				'files/for-section/a' => [
					'title' => 'Type A'
				],
				'files/for-section/b' => [
					'title' => 'Type B'
				],
				'files/for-fields/a' => [
					'title' => 'Field Type A'
				],
				'files/for-fields/b' => [
					'title' => 'Field Type B'
				],
				'files/for-fields/c' => [
					'title' => 'Field Type C',
					'accept' => 'image'
				],
				'files/for-fields/d' => [
					'title' => 'Field Type D'
				],
				'files/for-fields/e' => [
					'title' => 'Field Type E'
				],
				'files/for-fields/f' => [
					'title' => 'Field Type F'
				],
				'files/current' => [
					'title' => 'Just the current'
				]
			]
		]);

		$page = new Page([
			'slug'    => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename' => 'test.pdf',
			'content'  => ['template' => 'current'],
			'parent'   => $page
		]);

		$blueprints = $file->blueprints();
		$this->assertCount(9, $blueprints);
		$this->assertSame('default', $blueprints[0]['name']);
		$this->assertSame('for-fields/a', $blueprints[1]['name']);
		$this->assertSame('for-fields/b', $blueprints[2]['name']);
		$this->assertSame('for-fields/d', $blueprints[3]['name']);
		$this->assertSame('for-fields/e', $blueprints[4]['name']);
		$this->assertSame('for-fields/f', $blueprints[5]['name']);
		$this->assertSame('current', $blueprints[6]['name']);
		$this->assertSame('for-section/a', $blueprints[7]['name']);
		$this->assertSame('for-section/b', $blueprints[8]['name']);
	}

	public function testBlueprintsInSection(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'sections' => [
						'section-a' => [
							'type' => 'files',
							'template' => 'for-section/a'
						],
						'section-b' => [
							'type' => 'files',
							'template' => 'for-section/b'
						],
						'section-c' => [
							'type' => 'fields',
							'fields' => [
								[
									'type' => 'files'
								],
								[
									'type'    => 'files',
									'uploads' => 'for-fields/a'
								],
								[
									'type'    => 'files',
									'uploads' => [
										'template' => 'for-fields/b'
									]
								],
								[
									'type'    => 'files',
									'uploads' => [
										'parent'   => 'foo',
										'template' => 'for-fields/c'
									]
								],
								[
									'type'    => 'files',
									'uploads' => 'for-fields/c'
								]
							]
						]
					]
				],
				'files/for-section/a' => [
					'title' => 'Type A'
				],
				'files/for-section/b' => [
					'title' => 'Type B'
				],
				'files/for-fields/a' => [
					'title' => 'Field Type A'
				],
				'files/for-fields/b' => [
					'title' => 'Field Type B'
				],
				'files/for-fields/c' => [
					'title' => 'Field Type C',
					'accept' => 'image'
				],
				'files/current' => [
					'title' => 'Just the current'
				]
			]
		]);

		$page = new Page([
			'slug'    => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename' => 'test.pdf',
			'content'  => ['template' => 'current'],
			'parent'   => $page
		]);


		$blueprints = $file->blueprints('section-a');
		$this->assertCount(2, $blueprints);
		$this->assertSame('current', $blueprints[0]['name']);
		$this->assertSame('for-section/a', $blueprints[1]['name']);

		$blueprints = $file->blueprints('section-c');
		$this->assertCount(4, $blueprints);
		$this->assertSame('default', $blueprints[0]['name']);
		$this->assertSame('for-fields/a', $blueprints[1]['name']);
		$this->assertSame('for-fields/b', $blueprints[2]['name']);
		$this->assertSame('current', $blueprints[3]['name']);
	}
}
