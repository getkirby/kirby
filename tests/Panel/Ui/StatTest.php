<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Stat::class)]
class StatTest extends TestCase
{
	protected ModelWithContent $model;

	public function setUp(): void
	{
		$this->model = new class (['slug' => 'test']) extends Page {
			public function report(): array
			{
				return [
					'label' => 'Test Query Label',
					'value' => 'Test Query Value',
				];
			}
		};
	}

	public function assertProp(
		string $prop,
		bool $translatable = true,
		bool $queryable = true,
		bool $nullable = true
	): void {
		$stat = new Stat(
			...[
				'label' => 'Test Label',
				'value' => 'Test Value',
				'model' => $this->model,
				$prop => 'Test'
			]
		);

		$this->assertSame('Test', $stat->$prop());

		if ($nullable === true) {
			$stat = new Stat(
				model: $this->model,
				label: 'Test Label',
				value: 'Test Value',
			);
			$this->assertNull($stat->$prop());
		}

		if ($translatable === true) {
			$stat = new Stat(
				...[
					'model' => $this->model,
					'label' => 'Test Label',
					'value' => 'Test Value',
					$prop => [
						'en' => 'Test'
					]
				]
			);

			$this->assertSame('Test', $stat->$prop());
		}

		if ($queryable === true) {
			$stat = new Stat(
				...[
					'model' => $this->model,
					'label' => 'Test Label',
					'value' => 'Test Value',
					$prop => '{{ page.slug }}'
				]
			);

			$this->assertSame('test', $stat->$prop());
		}
	}

	public function testComponent(): void
	{
		$stat = new Stat(
			label: 'Test Label',
			value: 'Test Value'
		);
		$this->assertSame('k-stat', $stat->component());

		$stat = new Stat(
			model: $this->model,
			label: 'Test Label',
			value: 'Test Value'
		);
		$this->assertSame('k-stat', $stat->component());

		$stat = new Stat(
			model: $this->model,
			label: 'Test Label',
			value: 'Test Value',
			component: 'k-stat-test'
		);

		$this->assertSame('k-stat-test', $stat->component());
	}

	public function testDialog(): void
	{
		$this->assertProp(
			prop: 'dialog',
			nullable: true,
			translatable: true,
			queryable: true
		);
	}

	public function testDrawer(): void
	{
		$this->assertProp(
			prop: 'drawer',
			nullable: true,
			translatable: true,
			queryable: true
		);
	}

	public function testFrom(): void
	{
		// from array with model
		$stat = Stat::from(
			input: [
				'label' => 'Test Label',
				'value' => 'Test Value',
			],
			model: $this->model
		);

		$this->assertInstanceOf(Stat::class, $stat);
		$this->assertSame('Test Label', $stat->label());
		$this->assertSame('Test Value', $stat->value());
		$this->assertSame($this->model, $stat->model);


		// from array without model
		$stat = Stat::from(
			input: [
				'label' => 'Test Label',
				'value' => 'Test Value',
			],
			model: null
		);

		$this->assertInstanceOf(Stat::class, $stat);
		$this->assertSame('Test Label', $stat->label());
		$this->assertSame('Test Value', $stat->value());
		$this->assertNull($stat->model);

		// from query
		$stat = Stat::from(
			input: 'page.report',
			model: $this->model
		);

		$this->assertInstanceOf(Stat::class, $stat);
		$this->assertSame('Test Query Label', $stat->label());
		$this->assertSame('Test Query Value', $stat->value());
	}

	public function testFromWithInvalidInput(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid data from stat query. The query must return an array.');

		Stat::from(
			input: 'invalid',
			model: $this->model
		);
	}

	public function testIcon(): void
	{
		$this->assertProp(
			prop: 'icon',
			nullable: true,
			translatable: false,
			queryable: true
		);
	}

	public function testInfo(): void
	{
		$this->assertProp(
			prop: 'info',
			nullable: true,
			translatable: true,
			queryable: true
		);
	}

	public function testLabel(): void
	{
		$this->assertProp(
			prop: 'label',
			nullable: false,
			translatable: true,
			queryable: true
		);
	}

	public function testLink(): void
	{
		$this->assertProp(
			prop: 'link',
			nullable: true,
			translatable: true,
			queryable: true
		);
	}

	public function testTheme(): void
	{
		$this->assertProp(
			prop: 'theme',
			nullable: true,
			translatable: false,
			queryable: true
		);
	}

	public function testValue(): void
	{
		$this->assertProp(
			prop: 'value',
			nullable: false,
			translatable: true,
			queryable: true,
		);
	}
}
