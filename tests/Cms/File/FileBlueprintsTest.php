<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileBlueprintsTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileBlueprints';

	public function testBlueprints(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'list-a' => [
							'type'     => 'filelist',
							'template' => 'for-list/a'
						],
						'list-b' => [
							'type'     => 'filelist',
							'template' => 'for-list/b'
						],
						'list-c' => [
							'type'     => 'filelist',
							'template' => 'not-exist'
						],
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
								'parent'   => 'site',
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
				],
				'files/for-list/a' => [
					'title' => 'Type A'
				],
				'files/for-list/b' => [
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
		$this->assertSame('for-list/a', $blueprints[7]['name']);
		$this->assertSame('for-list/b', $blueprints[8]['name']);
	}

	public function testBlueprintsInField(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'fields' => [
						'list-a' => [
							'type'     => 'filelist',
							'template' => 'for-list/a'
						],
						'list-b' => [
							'type'     => 'filelist',
							'template' => 'for-list/b'
						],
						'field-a' => [
							'type' => 'files'
						],
						'field-b' => [
							'type'    => 'files',
							'uploads' => 'for-fields/a'
						],
						'field-c' => [
							'type'    => 'files',
							'uploads' => [
								'template' => 'for-fields/b'
							]
						],
						'field-d' => [
							'type'    => 'files',
							'uploads' => [
								'parent'   => 'site',
								'template' => 'for-fields/c'
							]
						],
						'field-e' => [
							'type'    => 'files',
							'uploads' => 'for-fields/c'
						]
					]
				],
				'files/for-list/a' => [
					'title' => 'Type A'
				],
				'files/for-list/b' => [
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

		// a file list field only accepts its own template
		$blueprints = $file->blueprints('list-a');
		$this->assertCount(2, $blueprints);
		$this->assertSame('current', $blueprints[0]['name']);
		$this->assertSame('for-list/a', $blueprints[1]['name']);

		// fields with uploads have to be addressed individually
		$blueprints = $file->blueprints('field-a');
		$this->assertCount(2, $blueprints);
		$this->assertSame('default', $blueprints[0]['name']);
		$this->assertSame('current', $blueprints[1]['name']);

		$blueprints = $file->blueprints('field-b');
		$this->assertCount(2, $blueprints);
		$this->assertSame('for-fields/a', $blueprints[0]['name']);
		$this->assertSame('current', $blueprints[1]['name']);

		$blueprints = $file->blueprints('field-c');
		$this->assertCount(2, $blueprints);
		$this->assertSame('for-fields/b', $blueprints[0]['name']);
		$this->assertSame('current', $blueprints[1]['name']);

		// the uploads parent is not this model
		$blueprints = $file->blueprints('field-d');
		$this->assertCount(1, $blueprints);
		$this->assertSame('current', $blueprints[0]['name']);
	}
}
