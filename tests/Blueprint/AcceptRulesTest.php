<?php

namespace Kirby\Blueprint;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AcceptRules::class)]
class AcceptRulesTest extends BlueprintTest
{
	public function testFileBlueprintsDefault(): void
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

		$this->assertSame(['default'], $rules->fileBlueprints());
	}

	public function testFileBlueprintsFromFields(): void
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
						'blueprint' => 'a'
					]
				],
				'b' => [
					'type' => 'textarea',
					'uploads' => [
						'blueprint' => 'b'
					]
				],
				'c' => [
					'type'   => 'structure',
					'fields' => [
						'text' => [
							'type' => 'textarea',
							'uploads' => [
								'blueprint' => 'c'
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
								'blueprint' => 'd'
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
										'blueprint' => 'e'
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

		$this->assertSame(['a', 'b', 'c', 'd', 'e'], $rules->fileBlueprints());
	}

	public function testFileBlueprintsFromFieldsWithUnknownType(): void
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
										'blueprint' => 'a'
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
		$this->assertSame([], $rules->fileBlueprints());
	}

	public function testFileBlueprintsFromFieldsWithDifferentParent(): void
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
						'parent'    => 'site',
						'blueprint' => 'a'
					]
				],
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame([], $rules->fileBlueprints());
	}

	public function testFileBlueprintsFromFieldsAndFileLists(): void
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
						'blueprint' => 'a'
					]
				],
				'b' => [
					'type' => 'textarea',
					'uploads' => [
						'blueprint' => 'b'
					]
				],
				'files' => [
					'type'      => 'filelist',
					'blueprint' => 'c'
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a', 'b', 'c'], $rules->fileBlueprints());
	}

	public function testFileBlueprintsFromFieldsets(): void
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
			$rules->fileBlueprints()
		);
	}

	public function testFileBlueprintsFromDefaultFieldsets(): void
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
		$this->assertContains('blocks/image', $rules->fileBlueprints());
	}

	public function testFileBlueprintsInField(): void
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
					'type'      => 'filelist',
					'blueprint' => 'a'
				],
				'b' => [
					'type'      => 'filelist',
					'blueprint' => 'b'
				],
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a'], $rules->fileBlueprints('a'));
	}

	public function testFileBlueprintsFromFileLists(): void
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
					'type'      => 'filelist',
					'blueprint' => 'a'
				],
				'b' => [
					'type'      => 'filelist',
					'blueprint' => 'b'
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a', 'b'], $rules->fileBlueprints());
	}

	public function testFileBlueprintsFromAllAvailable(): void
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

		// a file list without blueprint accepts all available blueprints
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'fields' => [
				'files' => [
					'type' => 'filelist',
					// no blueprint specified - should get all available
				],
			]
		]);

		$rules = new AcceptRules($blueprint);

		$expected = ['default', 'image', 'document', 'video'];
		$result   = $rules->fileBlueprints();
		sort($expected);
		sort($result);
		$this->assertSame($expected, $result);
	}

	public function testPageBlueprints(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'name'   => 'default',
			'fields' => [
				'albums' => [
					'type'       => 'pagelist',
					'blueprints' => ['album']
				],
				'notes' => [
					'type'   => 'pagelist',
					'create' => 'note'
				],
				'object' => [
					'type'   => 'object',
					'fields' => [
						'events' => [
							'type'       => 'pagelist',
							'blueprints' => 'event'
						]
					]
				],
				// no pages can be added
				'archive' => [
					'type'   => 'pagelist',
					'create' => false
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['album', 'note', 'event'], $rules->pageBlueprints());

		// the result is cached
		$this->assertSame(['album', 'note', 'event'], $rules->pageBlueprints());
	}

	public function testPageBlueprintsInField(): void
	{
		$blueprint = new Blueprint([
			'model'  => $this->model,
			'name'   => 'default',
			'fields' => [
				'albums' => [
					'type'       => 'pagelist',
					'blueprints' => ['album']
				],
				'notes' => [
					'type'       => 'pagelist',
					'blueprints' => ['note']
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['note'], $rules->pageBlueprints('notes'));
		$this->assertSame([], $rules->pageBlueprints('does-not-exist'));
	}

	public function testPageBlueprintsWithoutFields(): void
	{
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default'
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame([], $rules->pageBlueprints());
	}
}
