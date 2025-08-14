<?php

namespace Kirby\Api;

use Kirby\Cms\File;
use Kirby\Cms\FileBlueprint;
use Kirby\Cms\Page;

class FileBlueprintModelTest extends ModelTestCase
{
	protected File $file;

	public function setUp(): void
	{
		parent::setUp();

		$page = new Page([
			'slug' => 'test'
		]);

		$this->file = new File(['filename' => 'test.jpg', 'parent' => $page]);
	}

	public function testName(): void
	{
		$blueprint = new FileBlueprint([
			'name'  => 'test',
			'model' => $this->file
		]);

		$this->assertAttr($blueprint, 'name', 'test');
	}

	public function testOptions(): void
	{
		$blueprint = new FileBlueprint([
			'name'  => 'test',
			'model' => $this->file
		]);

		$options = $this->attr($blueprint, 'options');

		$this->assertArrayHasKey('changeName', $options);
		$this->assertArrayHasKey('create', $options);
		$this->assertArrayHasKey('delete', $options);
		$this->assertArrayHasKey('replace', $options);
		$this->assertArrayHasKey('update', $options);
	}

	public function testTabs(): void
	{
		$blueprint = new FileBlueprint([
			'name'  => 'test',
			'model' => $this->file
		]);

		$this->assertAttr($blueprint, 'tabs', []);
	}

	public function testTitle(): void
	{
		$blueprint = new FileBlueprint([
			'name'  => 'test',
			'title' => 'Test',
			'model' => $this->file
		]);

		$this->assertAttr($blueprint, 'title', 'Test');
	}
}
