<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Stat::class)]
class StatTest extends TestCase
{
	protected ModelWithContent $model;

	public function setUp(): void
	{
		$this->model = new Page(['slug' => 'test']);
	}

	public function assertProp(
		string $prop,
		bool $translatable = true,
		bool $queryable = true,
		bool $nullable = true
	): void {
		$stat = new Stat(
			...[
				'model' => $this->model,
				$prop => 'Test'
			]
		);

		$this->assertSame('Test', $stat->$prop());

		if ($nullable === true) {
			$stat = new Stat(model: $this->model);
			$this->assertNull($stat->$prop());
		}

		if ($translatable === true) {
			$stat = new Stat(
				...[
					'model' => $this->model,
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
					$prop => '{{ page.slug }}'
				]
			);

			$this->assertSame('test', $stat->$prop());
		}
	}

	public function testComponent(): void
	{
		$stat = new Stat(model: $this->model);
		$this->assertSame('k-stat', $stat->component());

		$stat = new Stat(model: $this->model, component: 'k-stat-test');
		$this->assertSame('k-stat-test', $stat->component());
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
			nullable: true,
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
			nullable: true,
			translatable: true,
			queryable: true
		);
	}
}
