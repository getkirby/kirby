<?php

namespace Kirby\Blueprint;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AcceptRules::class)]
class AcceptRulesTest extends BlueprintTest
{
	public function testAcceptedFileTemplatesDefault(): void
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

		$this->assertSame(['default'], $rules->acceptedFileTemplates());
	}

	public function testAcceptedFileTemplatesFromFields(): void
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

		$this->assertSame(['a', 'b', 'c', 'd', 'e'], $rules->acceptedFileTemplates());
	}

	public function testAcceptedFileTemplatesFromFieldsAndSections(): void
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

		$this->assertSame(['a', 'b', 'c'], $rules->acceptedFileTemplates());
	}

	public function testAcceptedFileTemplatesFromSection(): void
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
			]
		]);

		$rules = new AcceptRules($blueprint);

		$this->assertSame(['a'], $rules->acceptedFileTemplates('a'));
	}

	public function testAcceptedFileTemplatesFromSections(): void
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

		$this->assertSame(['a', 'b'], $rules->acceptedFileTemplates());
	}
}
