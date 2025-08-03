<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Stats::class)]
class StatsTest extends TestCase
{
	protected ModelWithContent $model;

	public function setUp(): void
	{
		$this->model = new Page(['slug' => 'test']);
	}

	public function testFrom(): void
	{
		$stats = Stats::from(
			model: $this->model,
			reports: [],
			size: 'large'
		);

		$this->assertInstanceOf(Stats::class, $stats);
		$this->assertSame([], $stats->reports());
		$this->assertSame('large', $stats->size());
	}

	public function testFromQuery(): void
	{
		$stats = Stats::from(
			model: new Page([
				'slug' => 'test',
				'content' => [
					'reports' => [
						[
							'label' => 'test',
							'value' => 'test',
						]
					],
				],
			]),
			reports: 'page.reports.yaml'
		);

		$this->assertSame([
			[
				'dialog' => null,
				'drawer' => null,
				'icon'   => null,
				'info'   => null,
				'label'  => 'test',
				'link'   => null,
				'theme'  => null,
				'value'  => 'test',
			],
		], $stats->reports());
	}

	public function testFromQueryWithInvalidResult(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid data from stats query. The query must return an array.');

		Stats::from(
			model: new Page([
				'slug' => 'test',
				'content' => [
					'reports' => 'foo'
				],
			]),
			reports: 'page.reports'
		);
	}

	public function testProps(): void
	{
		$stats = new Stats(
			reports: [
				[
					'label' => 'test',
					'value' => 'test',
				],
			],
			size: 'large'
		);

		$this->assertSame([
			'reports' => [
				[
					'dialog' => null,
					'drawer' => null,
					'icon'   => null,
					'info'   => null,
					'label'  => 'test',
					'link'   => null,
					'theme'  => null,
					'value'  => 'test',
				],
			],
			'size' => 'large',
		], $stats->props());
	}

	public function testReports(): void
	{
		$stats = new Stats(
			reports: [
				[
					'label' => 'test',
					'value' => 'test',
				],
			],
			size: 'medium'
		);

		$this->assertSame([
			[
				'dialog' => null,
				'drawer' => null,
				'icon'   => null,
				'info'   => null,
				'label'  => 'test',
				'link'   => null,
				'theme'  => null,
				'value'  => 'test',
			],
		], $stats->reports());
	}

	public function testReportsWithStatObject(): void
	{
		$stats = new Stats(
			reports: [
				new Stat(
					label: 'test',
					value: 'test',
				),
			],
			size: 'medium'
		);

		$this->assertSame([
			[
				'dialog' => null,
				'drawer' => null,
				'icon'   => null,
				'info'   => null,
				'label'  => 'test',
				'link'   => null,
				'theme'  => null,
				'value'  => 'test',
			],
		], $stats->reports());
	}

	public function testReportsWithModel(): void
	{
		$stats = new Stats(
			model: $this->model,
			reports: [
				[
					'label' => 'test',
					'value' => 'test',
				],
			],
			size: 'medium'
		);

		$this->assertSame([
			[
				'dialog' => null,
				'drawer' => null,
				'icon'   => null,
				'info'   => null,
				'label'  => 'test',
				'link'   => null,
				'theme'  => null,
				'value'  => 'test',
			],
		], $stats->reports());
	}

	public function testSize(): void
	{
		$stats = new Stats(
			model: $this->model,
			reports: [],
			size: 'medium'
		);

		$this->assertSame('medium', $stats->size());
	}
}
