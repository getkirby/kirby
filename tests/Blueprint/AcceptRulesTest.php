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
			'sections' => [
				'files' => [
					'type' => 'files',
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
					'type' => 'files',
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
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a', 'b', 'c', 'd', 'e'], $rules->fileTemplates());
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
					'type' => 'files',
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

	public function testFileTemplatesFromFieldsAndSections(): void
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
			'sections' => [
				'fields' => [
					'type' => 'fields',
					'fields' => [
						'a' => [
							'type' => 'files',
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
					],
				],
				'files' => [
					'type'     => 'files',
					'template' => 'c'
				]
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a', 'b', 'c'], $rules->fileTemplates());
	}

	public function testFileTemplatesFromSection(): void
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
			'sections' => [
				'a' => [
					'type'     => 'files',
					'template' => 'a'
				],
				'b' => [
					'type'     => 'files',
					'template' => 'b'
				],
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a'], $rules->fileTemplates('a'));
	}

	public function testFileTemplatesFromSections(): void
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
			'sections' => [
				'a' => [
					'type'     => 'files',
					'template' => 'a'
				],
				'b' => [
					'type'     => 'files',
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

		// Files section without template should include all available templates
		$blueprint = new Blueprint([
			'model' => $this->model,
			'name'  => 'default',
			'sections' => [
				'files' => [
					'type' => 'files',
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
