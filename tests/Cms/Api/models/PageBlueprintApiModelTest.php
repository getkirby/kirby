<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class PageBlueprintApiModelTest extends ApiModelTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->page = new Page(['slug' => 'test']);
	}

	public function testName(): void
	{
		$blueprint = new PageBlueprint([
			'name'  => 'test',
			'model' => $this->page
		]);

		$this->assertAttr($blueprint, 'name', 'test');
	}

	public function testNum(): void
	{
		$blueprint = new PageBlueprint([
			'name'  => 'test',
			'model' => $this->page,
			'num'   => '{{ page.year }}'
		]);

		$this->assertAttr($blueprint, 'num', '{{ page.year }}');
	}

	public function testOptions(): void
	{
		$blueprint = new PageBlueprint([
			'name'  => 'test',
			'model' => $this->page
		]);

		$options = $this->attr($blueprint, 'options');

		$this->assertArrayHasKey('access', $options);
		$this->assertArrayHasKey('changeSlug', $options);
		$this->assertArrayHasKey('changeStatus', $options);
		$this->assertArrayHasKey('changeTemplate', $options);
		$this->assertArrayHasKey('changeTitle', $options);
		$this->assertArrayHasKey('create', $options);
		$this->assertArrayHasKey('delete', $options);
		$this->assertArrayHasKey('duplicate', $options);
		$this->assertArrayHasKey('list', $options);
		$this->assertArrayHasKey('preview', $options);
		$this->assertArrayHasKey('read', $options);
		$this->assertArrayHasKey('sort', $options);
		$this->assertArrayHasKey('update', $options);
	}

	public function testPreview(): void
	{
		$blueprint = new PageBlueprint([
			'name'    => 'test',
			'model'   => $this->page,
			'options' => [
				'preview' => 'test'
			]
		]);

		$this->assertAttr($blueprint, 'preview', 'test');
	}

	public function testStatus(): void
	{
		$blueprint = new PageBlueprint([
			'name'    => 'test',
			'model'   => $this->page,
			'status'  => $status = [
				'draft' => [
					'label' => 'Test',
					'text'  => 'Test'
				],
			]
		]);

		$this->assertAttr($blueprint, 'status', $status);
	}

	public function testTabs(): void
	{
		$blueprint = new PageBlueprint([
			'name'  => 'test',
			'model' => $this->page
		]);

		$this->assertAttr($blueprint, 'tabs', []);
	}

	public function testTitle(): void
	{
		$blueprint = new PageBlueprint([
			'name'  => 'test',
			'title' => 'Test',
			'model' => $this->page
		]);

		$this->assertAttr($blueprint, 'title', 'Test');
	}
}
