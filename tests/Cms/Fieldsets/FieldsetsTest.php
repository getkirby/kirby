<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class FieldsetsTest extends TestCase
{
	public function testExtendGroups(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'blocks/testgroup' => [
					'name' => 'Text',
					'type' => 'group',
					'fieldsets' => [
						'heading',
						'text'
					]
				]
			]
		]);

		$fieldsets = Fieldsets::factory([
			'test' => [
				'extends' => 'blocks/testgroup'
			]
		]);

		$this->assertCount(2, $fieldsets);
		$this->assertSame('heading', $fieldsets->first()->type());
		$this->assertSame('text', $fieldsets->last()->type());

		$this->assertCount(1, $fieldsets->groups());
		$this->assertSame(['heading', 'text'], $fieldsets->groups()['test']['sets']);
	}

	public function testExtendsTabsOverwrite(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'blocks/foo' => [
					'name' => 'Text',
					'tabs' => [
						'content' => [
							'fields' => [
								'text' => ['type' => 'textarea'],
							]
						],
						'seo' => [
							'fields' => [
								'metaTitle' => ['type' => 'text'],
								'metaDescription' => ['type' => 'text']
							]
						]
					]
				]
			]
		]);

		$fieldsets = Fieldsets::factory([
			'bar' => [
				'extends' => 'blocks/foo',
				'tabs' => [
					'seo' => false
				]
			]
		]);

		$fieldset = $fieldsets->first();

		$this->assertIsArray($fieldset->tabs());
		$this->assertArrayHasKey('content', $fieldset->tabs());
		$this->assertArrayNotHasKey('seo', $fieldset->tabs());
	}

	public function testGroupsWithAutoLabels()
	{
		$fieldsets = Fieldsets::factory([
			'mediaBlocks' => [
				'type' => 'group',
				'fieldsets' => [
					'image'
				]
			]
		]);

		$this->assertSame('Media blocks', $fieldsets->groups()['mediaBlocks']['label']);
	}

}
