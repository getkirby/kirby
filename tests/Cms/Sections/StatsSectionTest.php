<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class MockPageForStatsSection extends Page
{
	public function report()
	{
		return $this->reports()[0];
	}

	public function reports(): array
	{
		return [
			[
				'icon'  => 'heart',
				'info'  => 'Info A',
				'label' => 'A',
				'link'  => 'https://getkirby.com',
				'theme' => null,
				'value' => 'Value A',
			],
			[
				'icon'  => null,
				'info'  => null,
				'label' => 'B',
				'link'  => null,
				'theme' => null,
				'value' => 'Value B',
			]
		];
	}
}

class StatsSectionTest extends TestCase
{
	protected $app;
	protected $model;

	public function setUp(): void
	{
		App::destroy();

		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->model = new MockPageForStatsSection(['slug' => 'test']);
	}

	public function testHeadline()
	{
		// single headline
		$section = new Section('stats', [
			'name'  => 'test',
			'model' => $this->model,
			'label' => 'Test'
		]);

		$this->assertSame('Test', $section->headline());

		// translated headline
		$section = new Section('stats', [
			'name'  => 'test',
			'model' => $this->model,
			'label' => [
				'en' => 'Stats',
				'de' => 'Statistik'
			]
		]);

		$this->assertSame('Stats', $section->headline());
	}

	public function testReports()
	{
		$section = new Section('stats', [
			'name'     => 'test',
			'model'    => $this->model,
			'reports'  => $reports = $this->model->reports()
		]);

		$this->assertSame($reports, $section->reports());
	}

	public function testReportsFromQuery()
	{
		$section = new Section('stats', [
			'name'     => 'test',
			'model'    => $this->model,
			'reports'  => 'page.reports'
		]);

		$this->assertSame($this->model->reports(), $section->reports());
	}

	public function testReportsFromInvalidValue()
	{
		$section = new Section('stats', [
			'name'     => 'test',
			'model'    => $this->model,
			'reports'  => new \stdClass()
		]);

		$this->assertSame([], $section->reports());
	}


	public function testReportsWithQueries()
	{
		$section = new Section('stats', [
			'name'     => 'test',
			'model'    => $this->model,
			'reports'  => [
				'page.report'
			]
		]);

		$this->assertSame([$this->model->report()], $section->reports());
	}

	public function testReportsWithInvalidQueries()
	{
		$section = new Section('stats', [
			'name'     => 'test',
			'model'    => $this->model,
			'reports'  => [
				'page.somethingSomething'
			]
		]);

		$this->assertSame([], $section->reports());
	}

	public function testReportsTranslatedInfo()
	{
		$section = new Section('stats', [
			'name'     => 'test',
			'model'    => Page::factory([
				'slug'    => 'test',
				'content' => ['icon' => 'heart']
			]),
			'reports'  => [
				[
					'label' => 'C',
					'value' => 'Value C',
					'info'  => [
						'en' => 'Extra information',
						'de' => 'Zusatzinformation'
					],
					'icon'  => '{{ page.icon }}',
					'link'  => null,
					'theme' => null,
				]
			]
		]);

		$report = $section->reports()[0];

		$this->assertSame('C', $report['label']);
		$this->assertSame('Value C', $report['value']);
		$this->assertSame('Extra information', $report['info']);
		$this->assertSame('heart', $report['icon']);
	}
}
