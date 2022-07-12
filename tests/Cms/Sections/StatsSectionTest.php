<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class MockPageForStatsSection extends Page
{
	public function report()
	{
		return $this->reports()[0];
	}

	public function reports()
	{
		return [
			[
				'label' => 'A',
				'value' => 'Value A',
				'info'  => 'Info A',
				'link'  => 'https://getkirby.com',
				'theme' => null,
			],
			[
				'label' => 'B',
				'value' => 'Value B',
				'info'  => null,
				'link'  => null,
				'theme' => null,
			]
		];
	}
}

class StatsSectionTest extends TestCase
{
	protected $app;

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
			'name'     => 'test',
			'model'    => $this->model,
			'headline' => 'Test'
		]);

		$this->assertEquals('Test', $section->headline());

		// translated headline
		$section = new Section('stats', [
			'name'     => 'test',
			'model'    => $this->model,
			'headline' => [
				'en' => 'Stats',
				'de' => 'Statistik'
			]
		]);

		$this->assertEquals('Stats', $section->headline());
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
}
