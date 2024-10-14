<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\TestCase;

/**
 * @coversDefaultClass \Kirby\Form\Fields
 */
class FieldsTest extends TestCase
{
	protected App $app;
	protected Page $model;

	public function setUp(): void
	{
		$this->app   = App::instance();
		$this->model = new Page(['slug' => 'test']);
	}

	/**
	 * @covers ::__construct
	 */	
	public function testConstruct()
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'model' => $this->model
			],
			'b' => [
				'type'  => 'text',
				'model' => $this->model
			],
		]);

		$this->assertSame('a', $fields->first()->name());
		$this->assertSame('b', $fields->last()->name());
	}

	/**
	 * @covers ::defaults
	 */
	public function testDefaults()
	{
		$fields = new Fields([
			'a' => [
				'default' => 'a',
				'model'   => $this->model,
				'type'    => 'text'
			],
			'b' => [
				'default' => 'b',
				'model'   => $this->model,
				'type'    => 'text'
			],	
		]);

		$this->assertSame(['a' => 'a', 'b' => 'b'], $fields->defaults());
	}

	/**
	 * @covers ::toArray
	 */
	public function testToArray()
	{
		$fields = new Fields([
			'a' => [
				'type'  => 'text',
				'model' => $this->model
			],
			'b' => [
				'type'  => 'text',
				'model' => $this->model
			],	
		]);

		$this->assertSame(['a' => 'a', 'b' => 'b'], $fields->toArray(fn($field) => $field->name()));
	}	
}
