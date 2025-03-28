<?php

namespace Kirby\Cms;

use Kirby\Form\Fields;

class FieldsSectionTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FieldsSection';

	public function testFields()
	{
		$this->setUpSingleLanguage();
		$this->app->impersonate('kirby');

		$model = new Page([
			'slug' => 'test',
		]);

		// default language
		$section = new Section('fields', [
			'name' => 'test',
			'model' => $model,
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			]
		]);

		$expected = [
			'text' => [
				'autofocus'  => false,
				'counter'    => true,
				'disabled'   => false,
				'font'       => 'sans-serif',
				'hidden'     => false,
				'name'       => 'text',
				'required'   => false,
				'saveable'   => true,
				'spellcheck' => false,
				'translate'  => true,
				'type'       => 'text',
				'width'      => '1/1',
			]
		];

		$this->assertSame($expected, $section->fields());
	}

	public function testForm()
	{
		$this->setUpSingleLanguage();

		$model = new Page([
			'slug' => 'test',
		]);

		// default language
		$section = new Section('fields', [
			'name' => 'test',
			'model' => $model,
			'fields' => [
				'text' => [
					'type' => 'text'
				]
			]
		]);

		$this->assertInstanceOf(Fields::class, $section->form());
	}
}
