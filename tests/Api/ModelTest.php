<?php

namespace Kirby\Api;

use Exception;
use Kirby\TestCase;

class ModelTest extends TestCase
{
	public function testConstruct()
	{
		$model = new Model(new Api([]), [], []);

		$this->assertInstanceOf(Model::class, $model);
	}
	public function testConstructInvalidModel()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid model type "stdClass" expected: "nonexists"');

		new Model(new Api([]), new \stdClass(), ['type' => 'nonexists']);
	}

	public function testConstructMissingModel()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Missing model data');

		new Model(new Api([]), null, []);
	}

	public function testSelectInvalidKeys()
	{
		$model = new Model(new Api([]), [], []);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid select keys');
		$model->select(0);
	}

	public function testSelection()
	{
		$api = new Api([
			'models' => [
				'test' => [
					'fields' => [
						'key'   => fn ($model) => strtolower($model),
						'value' => fn ($model) => $model
					]
				]
			]
		]);

		// invalid select
		$model = new Model($api, [
			'foo' => 'A',
			'bar' => 'B',
			'baz' => 'C',
		], [
			'model'  => 'test',
			'select' => ['key']
		]);

		$selection = $model->selection();

		$this->assertSame(['key' => [
			'view'   => null,
			'select' => null
		]], $selection);

		// string select
		$model = new Model($api, [
			'foo' => 'A',
			'bar' => 'B',
			'baz' => 'C',
		], [
			'model'  => 'test',
			'select' => ['key' => 'value']
		]);

		$selection = $model->selection();

		$this->assertSame(['key' => [
			'view'   => 'value',
			'select' => null
		]], $selection);

		// array select
		$model = new Model($api, [
			'foo' => 'A',
			'bar' => 'B',
			'baz' => 'C',
		], [
			'model'  => 'test',
			'select' => ['key' => ['key', 'value']]
		]);

		$selection = $model->selection();

		$this->assertSame(['key' => [
			'view'   => null,
			'select' => ['key', 'value']
		]], $selection);

		// invalid view select
		$model = new Model($api, [
			'foo' => 'A',
			'bar' => 'B',
			'baz' => 'C',
		], [
			'model'  => 'test',
			'select' => ['key' => 'any']
		]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid sub view: "any"');

		$selection = $model->selection();
	}
}
