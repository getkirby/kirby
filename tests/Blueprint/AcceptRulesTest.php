<?php

namespace Kirby\Blueprint;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AcceptRules::class)]
class AcceptRulesTest extends BlueprintTest
{
	public function testFileTemplatesDefault(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'files' => [
					'type' => 'filelist',
				],
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['default'], $rules->fileTemplates());
	}

	public function testFileTemplatesFromFields(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				],
				'files/b' => [
					'name' => 'b',
				],
				'files/c' => [
					'name' => 'c',
				],
				'files/d' => [
					'name' => 'd',
				],
				'files/e' => [
					'name' => 'e',
				],
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'a' => [
					'type' => 'filepicker',
					'uploads' => [
						'template' => 'a'
					]
				],
				'b' => [
					'type' => 'textarea',
					'uploads' => [
						'template' => 'b'
					]
				],
				'c' => [
					'type'   => 'structure',
					'fields' => [
						'text' => [
							'type' => 'textarea',
							'uploads' => [
								'template' => 'c'
							]
						]
					]
				],
				'd' => [
					'type'   => 'object',
					'fields' => [
						'text' => [
							'type' => 'textarea',
							'uploads' => [
								'template' => 'd'
							]
						]
					]
				],
				'e' => [
					'type' => 'blocks',
					'fieldsets' => [
						'text' => [
							'fields' => [
								'text' => [
									'type' => 'textarea',
									'uploads' => [
										'template' => 'e'
									]
								]
							]
						]
					]
				],
				// uploads are disabled
				'f' => [
					'type'    => 'filepicker',
					'uploads' => false
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a', 'b', 'c', 'd', 'e'], $rules->fileTemplates());
	}

	public function testFileTemplatesFromFieldsWithUnknownType(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'name'   => 'default',
			'fields' => [
				'blocks' => [
					'type'      => 'blocks',
					'fieldsets' => [
						'text' => [
							'fields' => [
								'text' => [
									'type'    => 'does-not-exist',
									'uploads' => [
										'template' => 'a'
									]
								]
							]
						]
					]
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		// a field type that cannot be resolved becomes
		// an error field, which does not support uploads
		$this->assertSame([], $rules->fileTemplates());
	}

	public function testFileTemplatesFromFieldsWithDifferentParent(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				]
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'a' => [
					'type' => 'filepicker',
					'uploads' => [
						'parent'   => 'site',
						'template' => 'a'
					]
				],
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame([], $rules->fileTemplates());
	}

	public function testFileTemplatesFromFieldsAndFileLists(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				],
				'files/b' => [
					'name' => 'b',
				],
				'files/c' => [
					'name' => 'c',
				],
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'a' => [
					'type' => 'filepicker',
					'uploads' => [
						'template' => 'a'
					]
				],
				'b' => [
					'type' => 'textarea',
					'uploads' => [
						'template' => 'b'
					]
				],
				'files' => [
					'type'     => 'filelist',
					'template' => 'c'
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a', 'b', 'c'], $rules->fileTemplates());
	}

	public function testFileTemplatesFromFieldsets(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'blocks/hero' => [
					'fields' => [
						'image' => [
							'type'    => 'filepicker',
							'uploads' => 'hero'
						]
					]
				]
			]
		]);

		$blueprint = new Blueprint([
			'model'  => $this->model,
			'name'   => 'default',
			'fields' => [
				'a' => [
					'type'      => 'blocks',
					'fieldsets' => ['image']
				],
				'b' => [
					'type'      => 'blocks',
					'fieldsets' => [
						'hero' => true
					]
				],
				'c' => [
					'type'      => 'blocks',
					'fieldsets' => [
						'media' => [
							'type'      => 'group',
							'fieldsets' => ['hero']
						]
					]
				],
				'd' => [
					'type'      => 'layout',
					'fieldsets' => [
						'cover' => [
							'fields' => [
								'image' => [
									'type'    => 'filepicker',
									'uploads' => 'cover'
								]
							]
						]
					],
					'settings' => [
						'fields' => [
							'background' => [
								'type'    => 'filepicker',
								'uploads' => 'background'
							]
						]
					]
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(
			['blocks/image', 'hero', 'hero', 'cover', 'background'],
			$rules->fileTemplates()
		);
	}

	public function testFileTemplatesFromDefaultFieldsets(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'name'   => 'default',
			'fields' => [
				'blocks' => [
					'type' => 'blocks'
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		// the core image, gallery and video blocks
		// upload their files as `blocks/image`
		$this->assertContains('blocks/image', $rules->fileTemplates());
	}

	public function testFileTemplatesInField(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				]
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'a' => [
					'type'     => 'filelist',
					'template' => 'a'
				],
				'b' => [
					'type'     => 'filelist',
					'template' => 'b'
				],
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a'], $rules->fileTemplates('a'));
	}

	public function testFileTemplatesFromFileLists(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/a' => [
					'name' => 'a',
				],
				'files/b' => [
					'name' => 'b',
				],
			]
		]);

		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'a' => [
					'type'     => 'filelist',
					'template' => 'a'
				],
				'b' => [
					'type'     => 'filelist',
					'template' => 'b'
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a', 'b'], $rules->fileTemplates());
	}

	public function testFileTemplatesFromAllAvailable(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'files/image' => [
					'name' => 'image',
				],
				'files/document' => [
					'name' => 'document',
				],
				'files/video' => [
					'name' => 'video',
				],
			]
		]);

		// a file list without template accepts all available templates
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'files' => [
					'type' => 'filelist',
					// No template specified - should get all available
				],
			]
		]);

		$rules = new AcceptRules($blueprint);

		$expected = ['default', 'image', 'document', 'video'];
		$result   = $rules->fileTemplates();
		sort($expected);
		sort($result);
		$this->assertSame($expected, $result);
	}
}
